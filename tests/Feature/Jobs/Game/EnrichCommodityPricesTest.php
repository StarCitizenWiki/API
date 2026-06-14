<?php

declare(strict_types=1);

use App\Jobs\Game\EnrichCommodityPrices;
use App\Models\Game\Commodity\Commodity;
use App\Models\Game\GameVersion;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

afterEach(function (): void {
    Carbon::setTestNow();
});

it('updates commodity prices when the enriched source data changes', function (): void {
    Log::spy();

    $version = GameVersion::factory()->create(['is_default' => true, 'code' => '4.7.1']);
    $commodity = Commodity::factory()->create([
        'name' => 'Laranite',
        'uex_prices' => [
            ['uex_commodity_id' => 8, 'terminal_name' => 'Old Terminal', 'price_buy' => 1, 'price_sell' => 1, 'game_version' => '4.7.1', 'date_updated' => '2024-01-01T00:00:00+00:00'],
        ],
    ]);

    $priceBuy = 100;

    Http::fake(function ($request) use (&$priceBuy) {
        if (str_contains($request->url(), 'commodities_prices?id_commodity')) {
            return Http::response(['data' => [
                ['id_commodity' => 8, 'id_terminal' => 1, 'terminal_name' => 'New Terminal', 'terminal_code' => 'NT1', 'price_buy' => $priceBuy, 'price_sell' => 50, 'game_version' => '4.7.1', 'date_modified' => 1700000000],
            ]]);
        }

        return Http::response(status: 404);
    });

    Carbon::setTestNow('2024-01-01 10:00:00');
    (new EnrichCommodityPrices($version->id, [$commodity->id]))->handle();

    $firstUpdatedAt = $commodity->refresh()->updated_at;

    $priceBuy = 9999;

    Carbon::setTestNow('2024-01-01 11:00:00');
    (new EnrichCommodityPrices($version->id, [$commodity->id]))->handle();

    $commodity->refresh();

    expect($commodity->updated_at->getTimestamp())->toBeGreaterThan($firstUpdatedAt->getTimestamp())
        ->and($commodity->uex_prices[0]['price_buy'])->toBe(9999);
});
