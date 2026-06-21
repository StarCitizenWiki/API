<?php

declare(strict_types=1);

use App\Models\StarCitizen\PledgeStore\PledgeStoreSku;
use App\Models\StarCitizen\PledgeStore\PledgeStoreSkuHistory;
use Illuminate\Support\Str;

/*
 * Tests for the one-time backfill command that creates pledge_store_sku_history
 * entries from legacy shipmatrix_vehicle_skus data.
 *
 * The command matches by vehicle name + warbond flag, converts dollars to cents,
 * and uses the original created_at as the history timestamp.
 *
 * We set up the old table data directly and the new pledge_store_skus via factory.
 */

describe('backfill pledge store history', function (): void {
    it('creates history entries for matched SKUs with different prices', function (): void {
        $newSku = PledgeStoreSku::factory()->create([
            'name' => '300i',
            'product_id' => 72,
            'is_warbond' => true,
            'native_price' => 6000,
            'stock_available' => true,
        ]);

        // Legacy data: same vehicle name, warbond, different price
        insertOldSku('300i', 'Warbond Edition', 55, true, '2024-01-07 13:54:02');

        $this->artisan('pledge-store:backfill-history')
            ->assertSuccessful()
            ->expectsOutputToContain('1 history entries created');

        $history = PledgeStoreSkuHistory::where('pledge_store_sku_id', $newSku->id)
            ->where('created_at', '2024-01-07 13:54:02')
            ->first();

        expect($history)->not->toBeNull()
            ->and($history->getData('nativePrice.amount'))->toBe(5500)
            ->and($history->getData('stock.available'))->toBeTrue()
            ->and($history->getData('tags'))->toBe([['name' => 'Warbond']]);
        // $55 * 100
    });

    it('converts dollar prices to cents', function (): void {
        $newSku = PledgeStoreSku::factory()->create([
            'name' => 'Constellation Taurus',
            'product_id' => 72,
            'is_warbond' => false,
            'native_price' => 20000,
        ]);

        insertOldSku('Constellation Taurus', 'Standard Edition', 150, true, '2024-01-07 13:54:03');

        $this->artisan('pledge-store:backfill-history')
            ->assertSuccessful();

        $history = PledgeStoreSkuHistory::where('pledge_store_sku_id', $newSku->id)->first();

        expect($history->getData('nativePrice.amount'))->toBe(15000);
    });

    it('matches warbond editions correctly', function (): void {
        // Current prices differ from legacy prices so the assertion
        // would fail if backfill stored the current price instead of the legacy one.
        $standardSku = PledgeStoreSku::factory()->create([
            'name' => 'Gladius',
            'product_id' => 72,
            'is_warbond' => false,
            'native_price' => 12000,  // current: $120
        ]);
        $warbondSku = PledgeStoreSku::factory()->create([
            'name' => 'Gladius',
            'product_id' => 72,
            'is_warbond' => true,
            'native_price' => 11000,  // current: $110
        ]);

        // Legacy prices are $90 (standard) and $80 (warbond) -- deliberately
        // different from the current prices above.
        insertOldSku('Gladius', 'Standard Edition', 90, true, '2024-01-07 10:00:00');
        insertOldSku('Gladius', 'Warbond Edition', 80, true, '2024-01-07 10:00:01');

        $this->artisan('pledge-store:backfill-history')
            ->assertSuccessful();

        $standardHistory = PledgeStoreSkuHistory::where('pledge_store_sku_id', $standardSku->id)->first();
        $warbondHistory = PledgeStoreSkuHistory::where('pledge_store_sku_id', $warbondSku->id)->first();

        // History must contain the *legacy* price, not the current one.
        expect($standardHistory)->not->toBeNull()
            ->and($standardHistory->getData('nativePrice.amount'))->toBe(9000)
            ->and($warbondHistory)->not->toBeNull()
            ->and($warbondHistory->getData('nativePrice.amount'))->toBe(8000);
    });

    it('is idempotent - re-run creates no new entries', function (): void {
        $newSku = PledgeStoreSku::factory()->create([
            'name' => 'C1 Spirit',
            'product_id' => 72,
            'is_warbond' => true,
            'native_price' => 12500,
        ]);

        insertOldSku('C1 Spirit', 'Warbond Edition', 110, true, '2024-01-07 13:54:06');

        $this->artisan('pledge-store:backfill-history')->assertSuccessful();

        $countAfterFirst = PledgeStoreSkuHistory::where('pledge_store_sku_id', $newSku->id)->count();

        $this->artisan('pledge-store:backfill-history')
            ->assertSuccessful()
            ->expectsOutputToContain('0 history entries created');

        $countAfterSecond = PledgeStoreSkuHistory::where('pledge_store_sku_id', $newSku->id)->count();
        expect($countAfterSecond)->toBe($countAfterFirst);
    });

    it('skips old SKUs with no matching pledge store entry', function (): void {
        PledgeStoreSku::factory()->create([
            'name' => '300i',
            'product_id' => 72,
            'native_price' => 6000,
        ]);

        // Delisted ship that does not exist in pledge_store_skus
        insertOldSku('Bengal Carrier', 'Standard Edition', 5000, true, '2024-01-07 10:00:00');

        $this->artisan('pledge-store:backfill-history')
            ->assertSuccessful()
            ->expectsOutputToContain('1 no match');
    });

    it('handles multi-SKU vehicles with different warbond editions over time', function (): void {
        $newSku = PledgeStoreSku::factory()->create([
            'name' => 'Paladin',
            'product_id' => 72,
            'is_warbond' => true,
            'native_price' => 30000,
        ]);

        // Same vehicle, two different warbond SKUs from different dates with different prices
        insertOldSku('Paladin', 'Warbond Edition', 275, true, '2025-05-25 00:01:36');
        insertOldSku('Paladin', 'Warbond Edition', 325, true, '2025-10-12 00:00:56');

        $this->artisan('pledge-store:backfill-history')
            ->assertSuccessful();

        $histories = PledgeStoreSkuHistory::where('pledge_store_sku_id', $newSku->id)
            ->orderBy('created_at')
            ->get();

        expect($histories)->toHaveCount(2)
            ->and($histories[0]->getData('nativePrice.amount'))->toBe(27500)
            ->and($histories[1]->getData('nativePrice.amount'))->toBe(32500);
    });

    it('only matches against standalone ship SKUs (product_id 72)', function (): void {
        // Paint SKU with same name - should NOT be matched
        PledgeStoreSku::factory()->create([
            'name' => '300i',
            'product_id' => 268, // paints
            'native_price' => 500,
        ]);

        $shipSku = PledgeStoreSku::factory()->create([
            'name' => '300i',
            'product_id' => 72, // standalone ships
            'is_warbond' => false,
            'native_price' => 6000,
        ]);

        insertOldSku('300i', 'Standard Edition', 60, true, '2024-01-07 13:54:02');

        $this->artisan('pledge-store:backfill-history')
            ->assertSuccessful();

        $paintHistory = PledgeStoreSkuHistory::where('pledge_store_sku_id', PledgeStoreSku::where('product_id', 268)->first()->id)->first();
        $shipHistory = PledgeStoreSkuHistory::where('pledge_store_sku_id', $shipSku->id)->first();

        expect($paintHistory)->toBeNull()
            ->and($shipHistory)->not->toBeNull()
            ->and($shipHistory->getData('nativePrice.amount'))->toBe(6000);
    });

    it('tags subscriber editions correctly', function (): void {
        $newSku = PledgeStoreSku::factory()->create([
            'name' => 'Arrow',
            'product_id' => 72,
            'is_warbond' => false,
            'native_price' => 7500,
        ]);

        insertOldSku('Arrow', 'Imperator Subscribers Edition', 75, true, '2024-02-07 00:00:14');

        $this->artisan('pledge-store:backfill-history')
            ->assertSuccessful();

        $history = PledgeStoreSkuHistory::where('pledge_store_sku_id', $newSku->id)->first();

        expect($history)->not->toBeNull()
            ->and($history->getData('tags'))->toBe([['name' => 'Subscriber Exclusive']]);
    });
});

/*
 * Helpers
 */

function insertOldSku(string $vehicleName, string $skuTitle, int $priceDollars, bool $available, string $createdAt): void
{
    // Ensure the vehicle exists in shipmatrix_vehicles
    $vehicleId = DB::table('shipmatrix_vehicles')->where('name', $vehicleName)->value('id');

    if ($vehicleId === null) {
        $vehicleId = DB::table('shipmatrix_vehicles')->insertGetId([
            'cig_id' => rand(1000, 9999),
            'name' => $vehicleName,
            'slug' => Str::slug($vehicleName),
            'manufacturer_id' => 1,
            'production_status_id' => 1,
            'production_note_id' => 1,
            'size_id' => 1,
            'type_id' => 1,
            'chassis_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    DB::table('shipmatrix_vehicle_skus')->insert([
        'vehicle_id' => $vehicleId,
        'title' => $skuTitle,
        'price' => $priceDollars,
        'available' => $available,
        'cig_id' => rand(10000, 99999),
        'created_at' => $createdAt,
        'updated_at' => $createdAt,
    ]);
}
