<?php

declare(strict_types=1);

use App\Jobs\StarCitizen\Vehicle\ImportPledgeStore;
use App\Models\StarCitizen\PledgeStore\PledgeStoreSku;
use App\Models\StarCitizen\PledgeStore\PledgeStoreSkuHistory;
use App\Services\RsiDownloadClient;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/*
 * These tests exercise the sync/change-detection logic of ImportPledgeStore
 * by calling handle() with mocked HTTP responses.
 */

describe('initial import', function (): void {
    it('creates SKUs and history from API payload', function (): void {
        $payload = [
            makeSku(['id' => '100', 'name' => 'Aurora ES', 'nativePrice' => ['amount' => 2000, 'discounted' => null, 'discountDescription' => null]]),
            makeSku(['id' => '200', 'name' => 'Gladius', 'nativePrice' => ['amount' => 9000, 'discounted' => null, 'discountDescription' => null]]),
        ];

        runImport($payload);

        expect(PledgeStoreSku::count())->toBe(2)
            ->and(PledgeStoreSkuHistory::count())->toBe(2);

        $aurora = PledgeStoreSku::where('cig_id', 100)->first();
        expect($aurora)->not->toBeNull()
            ->and($aurora->name)->toBe('Aurora ES')
            ->and($aurora->native_price)->toBe(2000)
            ->and($aurora->stock_available)->toBeTrue();
    });

    it('stores warbond and discount data', function (): void {
        $payload = [
            makeSku([
                'id' => '300',
                'name' => 'Warbond Gladius',
                'isWarbond' => true,
                'nativePrice' => ['amount' => 9000, 'discounted' => 7500, 'discountDescription' => 'Warbond'],
            ]),
        ];

        runImport($payload);

        $sku = PledgeStoreSku::where('cig_id', 300)->first();
        expect($sku->is_warbond)->toBeTrue()
            ->and($sku->native_price)->toBe(9000)
            ->and($sku->native_discounted)->toBe(7500)
            ->and($sku->discount_description)->toBe('Warbond');

        $history = $sku->history()->first();
        expect($history->getData('nativePrice'))->toBe(['amount' => 9000, 'discounted' => 7500, 'discountDescription' => 'Warbond']);
    });

    it('stores tags in normalized column and full payload in data', function (): void {
        $payload = [
            makeSku([
                'id' => '400',
                'name' => 'Ironclad Pack',
                'tags' => [['name' => 'Bonus Gear'], ['name' => 'Limited Time']],
                'ships' => [['id' => '275', 'productionStatus' => 'flight-ready']],
            ]),
        ];

        runImport($payload);

        $sku = PledgeStoreSku::where('cig_id', 400)->first();
        expect($sku->tags)->toBe(['Bonus Gear', 'Limited Time']);
        expect($sku->getData('ships'))->toBe([['id' => '275', 'productionStatus' => 'flight-ready']]);
    });
});

describe('change detection', function (): void {
    it('records history when price changes', function (): void {
        $sku = PledgeStoreSku::factory()->create([
            'cig_id' => 100,
            'native_price' => 2000,
            'native_discounted' => null,
            'discount_description' => null,
            'stock_available' => true,
            'tags' => [],
        ]);
        PledgeStoreSkuHistory::create([
            'pledge_store_sku_id' => $sku->id,
            'data' => $sku->data?->toArray(),
        ]);

        $payload = [
            makeSku([
                'id' => '100',
                'name' => $sku->name,
                'nativePrice' => ['amount' => 2500, 'discounted' => null, 'discountDescription' => null],
            ]),
        ];

        runImport($payload);

        expect(PledgeStoreSkuHistory::count())->toBe(2);
        $sku->refresh();
        expect($sku->native_price)->toBe(2500);

        $latestHistory = $sku->latestHistory;
        expect($latestHistory->getData('nativePrice.amount'))->toBe(2500);
    });

    it('records history when discount appears', function (): void {
        $sku = PledgeStoreSku::factory()->create([
            'cig_id' => 100,
            'native_price' => 9000,
            'native_discounted' => null,
            'discount_description' => null,
            'stock_available' => true,
            'tags' => [],
        ]);
        PledgeStoreSkuHistory::create([
            'pledge_store_sku_id' => $sku->id,
            'data' => $sku->data?->toArray(),
        ]);

        $payload = [
            makeSku([
                'id' => '100',
                'name' => $sku->name,
                'nativePrice' => ['amount' => 9000, 'discounted' => 7200, 'discountDescription' => '20%'],
            ]),
        ];

        runImport($payload);

        $sku->refresh();
        expect($sku->native_discounted)->toBe(7200)
            ->and($sku->discount_description)->toBe('20%')
            ->and(PledgeStoreSkuHistory::count())->toBe(2);
    });

    it('records history when availability changes', function (): void {
        $sku = PledgeStoreSku::factory()->create([
            'cig_id' => 100,
            'native_price' => 2000,
            'native_discounted' => null,
            'discount_description' => null,
            'stock_available' => true,
            'tags' => [],
        ]);
        PledgeStoreSkuHistory::create([
            'pledge_store_sku_id' => $sku->id,
            'data' => $sku->data?->toArray(),
        ]);

        $payload = [
            makeSku([
                'id' => '100',
                'name' => $sku->name,
                'nativePrice' => ['amount' => 2000, 'discounted' => null, 'discountDescription' => null],
                'stock' => ['available' => false, 'unlimited' => false, 'qty' => 0, 'backOrder' => false, 'backOrderQty' => 0, 'level' => null],
            ]),
        ];

        runImport($payload);

        $sku->refresh();
        expect($sku->stock_available)->toBeFalse()
            ->and(PledgeStoreSkuHistory::count())->toBe(2);

        $latest = $sku->latestHistory;
        expect($latest->getData('stock.available'))->toBeFalse();
    });

    it('records history when tags change', function (): void {
        $sku = PledgeStoreSku::factory()->create([
            'cig_id' => 100,
            'native_price' => 2000,
            'stock_available' => true,
            'tags' => [],
        ]);
        PledgeStoreSkuHistory::create([
            'pledge_store_sku_id' => $sku->id,
            'data' => $sku->data?->toArray(),
        ]);

        $payload = [
            makeSku([
                'id' => '100',
                'name' => $sku->name,
                'tags' => [['name' => 'Limited Time']],
            ]),
        ];

        runImport($payload);

        expect(PledgeStoreSkuHistory::count())->toBe(2);
        $sku->refresh();
        expect($sku->tags)->toBe(['Limited Time']);
    });

    it('does not record history when nothing changed', function (): void {
        $sku = PledgeStoreSku::factory()->create([
            'cig_id' => 100,
            'native_price' => 2000,
            'native_discounted' => null,
            'discount_description' => null,
            'stock_available' => true,
            'tags' => [],
        ]);
        PledgeStoreSkuHistory::create([
            'pledge_store_sku_id' => $sku->id,
            'data' => $sku->data?->toArray(),
        ]);

        $payload = [
            makeSku([
                'id' => '100',
                'name' => $sku->name,
                'nativePrice' => ['amount' => 2000, 'discounted' => null, 'discountDescription' => null],
            ]),
        ];

        $historyBefore = PledgeStoreSkuHistory::count();

        runImport($payload);

        expect(PledgeStoreSkuHistory::count())->toBe($historyBefore);
    });

    it('updates non-tracked fields without history', function (): void {
        $sku = PledgeStoreSku::factory()->create([
            'cig_id' => 100,
            'name' => 'Old Name',
            'native_price' => 2000,
            'native_discounted' => null,
            'discount_description' => null,
            'stock_available' => true,
            'tags' => [],
        ]);
        PledgeStoreSkuHistory::create([
            'pledge_store_sku_id' => $sku->id,
            'data' => $sku->data?->toArray(),
        ]);

        $payload = [
            makeSku([
                'id' => '100',
                'name' => 'New Name',
                'nativePrice' => ['amount' => 2000, 'discounted' => null, 'discountDescription' => null],
            ]),
        ];

        $historyBefore = PledgeStoreSkuHistory::count();

        runImport($payload);

        $sku->refresh();
        expect($sku->name)->toBe('New Name')
            ->and(PledgeStoreSkuHistory::count())->toBe($historyBefore);
    });
});

describe('delisted items', function (): void {
    it('marks delisted SKUs as unavailable and records history', function (): void {
        $sku = PledgeStoreSku::factory()->create([
            'cig_id' => 999,
            'native_price' => 5000,
            'native_discounted' => null,
            'discount_description' => null,
            'stock_available' => true,
            'tags' => [],
        ]);
        PledgeStoreSkuHistory::create([
            'pledge_store_sku_id' => $sku->id,
            'data' => $sku->data?->toArray(),
        ]);

        // Return a completely different SKU - 999 is missing from API
        $payload = [
            makeSku([
                'id' => '100',
                'name' => 'Other Ship',
            ]),
        ];

        runImport($payload);

        $sku->refresh();
        expect($sku->stock_available)->toBeFalse();

        $latestHistory = $sku->latestHistory;
        expect($latestHistory->getData('stock.available'))->toBeFalse();
    });

    it('does not double-mark already unavailable SKUs', function (): void {
        $sku = PledgeStoreSku::factory()->create([
            'cig_id' => 999,
            'native_price' => 5000,
            'stock_available' => false,
            'tags' => [],
        ]);
        PledgeStoreSkuHistory::create([
            'pledge_store_sku_id' => $sku->id,
            'data' => $sku->data?->toArray(),
        ]);

        $payload = [
            makeSku(['id' => '100', 'name' => 'Other Ship']),
        ];

        runImport($payload);

        // SKU 999 should not have a new history entry - it was already unavailable
        expect($sku->history()->count())->toBe(1);
    });
});

describe('deduplication', function (): void {
    it('deduplicates SKUs by cig_id across categories', function (): void {
        $payload = [
            makeSku(['id' => '100', 'name' => 'Aurora']),
            makeSku(['id' => '100', 'name' => 'Aurora']),
            makeSku(['id' => '200', 'name' => 'Gladius']),
        ];

        runImport($payload);

        expect(PledgeStoreSku::count())->toBe(2)
            ->and(PledgeStoreSkuHistory::count())->toBe(2);
    });
});

describe('fetch failure safety', function (): void {
    it('fails the job without delisting when a category fetch fails', function (): void {
        $existing = PledgeStoreSku::factory()->create([
            'cig_id' => 999,
            'stock_available' => true,
            'tags' => [],
        ]);

        // Fake auth and context to succeed, but browse queries to fail
        Http::fake(function (Request $request) {
            $url = $request->url();

            if (Str::contains($url, 'setAuthToken') || Str::contains($url, 'setContextToken')) {
                return Http::response([], 200);
            }

            $body = json_decode($request->body(), true);
            $operation = $body['operationName'] ?? '';

            if ($operation === 'PlatformQuery') {
                return Http::response([
                    'data' => ['store' => ['context' => ['pricing' => [
                        'currencyCode' => 'USD', 'exponent' => 2,
                    ]]]],
                ], 200);
            }

            if ($operation === 'GetBrowseSkusByFilter') {
                return Http::response(['error' => 'Internal Server Error'], 500);
            }

            return Http::response([], 200);
        });

        $job = new ImportPledgeStore;
        $job->throttleUs = 0;
        $job->handle(new RsiDownloadClient);

        // The existing SKU should NOT be delisted - the job aborted safely
        $existing->refresh();
        expect($existing->stock_available)->toBeTrue()
            ->and(PledgeStoreSku::count())->toBe(1);
    });
});

/*
 * Helpers
 */

/**
 * Build a minimal API SKU payload, merging defaults with overrides.
 */
function makeSku(array $overrides = []): array
{
    return array_merge([
        'id' => $overrides['id'] ?? '100',
        'slug' => 'test-slug',
        'name' => $overrides['name'] ?? 'Test SKU',
        'title' => $overrides['name'] ?? 'Test SKU',
        'subtitle' => 'Test Category',
        'url' => '/pledge/test/test-sku',
        'type' => 'pledge',
        'nativePrice' => $overrides['nativePrice'] ?? ['amount' => 2000, 'discounted' => null, 'discountDescription' => null],
        'price' => ['amount' => 2000, 'discounted' => null, 'taxDescription' => [], 'discountDescription' => null],
        'stock' => $overrides['stock'] ?? ['unlimited' => true, 'show' => true, 'available' => true, 'backOrder' => false, 'qty' => 0, 'backOrderQty' => 0, 'level' => 'high'],
        'tags' => $overrides['tags'] ?? [],
        'label' => $overrides['name'] ?? 'Test SKU',
        'customizable' => false,
        'isWarbond' => $overrides['isWarbond'] ?? false,
        'isPackage' => false,
        'isVip' => false,
        'isDirectCheckout' => false,
        'productId' => '72',
        'ships' => $overrides['ships'] ?? [],
        'parentProduct' => ['slug' => 'test', 'type' => 'pledge_addons', 'name' => 'Test'],
        'publicType' => ['code' => 'pledge_ship_flyable', 'label' => 'Stand-Alone Flyable'],
        '__typename' => 'TySku',
    ], $overrides);
}

/**
 * Run the import job with mocked HTTP responses returning the given SKUs.
 */
function runImport(array $skus): void
{
    Http::fake(function (Request $request) use ($skus) {
        $url = $request->url();

        if (Str::contains($url, 'setAuthToken') || Str::contains($url, 'setContextToken')) {
            return Http::response([], 200);
        }

        $body = json_decode($request->body(), true);
        $operation = $body['operationName'] ?? '';

        if ($operation === 'PlatformQuery') {
            return Http::response([
                'data' => ['store' => ['context' => ['pricing' => [
                    'currencyCode' => 'USD', 'exponent' => 2,
                ]]]],
            ], 200);
        }

        if ($operation === 'GetBrowseSkusByFilter') {
            return Http::response([
                'data' => ['store' => ['listing' => [
                    'totalCount' => count($skus),
                    'resources' => $skus,
                ]]],
            ], 200);
        }

        return Http::response([], 200);
    });

    $job = new ImportPledgeStore;
    $job->throttleUs = 0;
    $job->handle(new RsiDownloadClient);
}
