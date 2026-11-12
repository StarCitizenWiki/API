<?php

declare(strict_types=1);

use App\Jobs\Game\ImportCommodityPrices;
use App\Models\Game\Commodity\Commodity;
use App\Models\Game\GameVersion;
use App\Models\Game\StarmapLocation;
use App\Models\Game\StarmapLocationData;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

describe('buildVersionPrefixMap', function (): void {
    it('works without previousVersionCode (null default)', function (): void {
        $version = GameVersion::factory()->create(['code' => '4.8.0-LIVE.1182']);

        // Call handle() with a minimal API response to exercise buildVersionPrefixMap
        Http::fake([
            'api.uexcorp.uk/*' => Http::response(['data' => []]),
        ]);

        Log::spy();

        $job = new ImportCommodityPrices($version->id);
        $job->handle();

        // If we get here without error, buildVersionPrefixMap succeeded without previousVersionCode
        Log::shouldHaveReceived('info');
    });

    it('filters prices matching previous version when provided', function (): void {
        Log::spy();

        $currentVersion = GameVersion::factory()->create(['code' => '4.8.0-LIVE.1182', 'is_default' => true]);
        $previousVersionCode = '4.7.0-LIVE.950';

        $commodity = Commodity::factory()->create(['name' => 'Laranite']);

        $location = StarmapLocation::factory()->create(['uuid' => 'aaa11111-2222-3333-4444-555566667777']);
        StarmapLocationData::factory()->create([
            'starmap_location_id' => $location->id,
            'game_version_id' => $currentVersion->id,
            'name' => 'Test Terminal Station',
        ]);

        Http::fake(function ($request) {
            if (str_contains($request->url(), 'commodities_prices_all')) {
                return Http::response([
                    'data' => [
                        [
                            'id_commodity' => 1,
                            'commodity_name' => 'Laranite',
                            'id_terminal' => 1,
                            'terminal_name' => 'Test Terminal',
                            'price_buy' => 100,
                            'price_sell' => 50,
                            'game_version' => '4.7.0-LIVE.950',
                            'date_modified' => 1700000000,
                        ],
                        [
                            'id_commodity' => 1,
                            'commodity_name' => 'Laranite',
                            'id_terminal' => 2,
                            'terminal_name' => 'Other Terminal',
                            'price_buy' => 200,
                            'price_sell' => 100,
                            'game_version' => '4.8.0-LIVE.1190',
                            'date_modified' => 1700000000,
                        ],
                        [
                            'id_commodity' => 1,
                            'commodity_name' => 'Laranite',
                            'id_terminal' => 3,
                            'terminal_name' => 'Old Terminal',
                            'price_buy' => 300,
                            'price_sell' => 150,
                            'game_version' => '3.24.0-LIVE.800',
                            'date_modified' => 1700000000,
                        ],
                    ],
                ]);
            }

            if (str_contains($request->url(), 'terminals')) {
                return Http::response([
                    'data' => [
                        [
                            'id' => 1,
                            'displayname' => 'Test Terminal Station',
                            'name' => 'Admin - Test Terminal',
                            'code' => 'TEST1',
                            'star_system_name' => 'Stanton',
                        ],
                    ],
                ]);
            }

            return Http::response(status: 404);
        });

        $job = new ImportCommodityPrices($currentVersion->id, $previousVersionCode);
        $job->handle();

        $prices = $commodity->refresh()->uex_prices;

        // Should include 4.7.x and 4.8.x but NOT 3.24.x
        expect($prices)->toBeArray()
            ->and($prices)->toHaveCount(2);

        $gameVersions = collect($prices)->pluck('game_version')->unique()->values()->toArray();

        expect($gameVersions)->toContain('4.8.0-LIVE.1182')
            ->and($gameVersions)->toContain($previousVersionCode)
            ->and($gameVersions)->not->toContain('3.24.0-LIVE.800');
    });
});

describe('handle', function (): void {
    it('imports prices for known commodities', function (): void {
        Log::spy();

        $version = GameVersion::factory()->create(['code' => '4.8.0-LIVE.1182', 'is_default' => true]);
        $commodity = Commodity::factory()->create(['name' => 'Quantainium']);

        $location = StarmapLocation::factory()->create(['uuid' => 'bbb22222-3333-4444-5555-666677778889']);
        StarmapLocationData::factory()->create([
            'starmap_location_id' => $location->id,
            'game_version_id' => $version->id,
            'name' => 'Quantainium Station',
        ]);

        Http::fake(function ($request) {
            if (str_contains($request->url(), 'commodities_prices_all')) {
                return Http::response([
                    'data' => [
                        [
                            'id_commodity' => 2,
                            'commodity_name' => 'Quantainium',
                            'id_terminal' => 1,
                            'terminal_name' => 'Test Terminal',
                            'price_buy' => 500,
                            'price_sell' => 250,
                            'date_modified' => 1700000000,
                        ],
                    ],
                ]);
            }

            if (str_contains($request->url(), 'terminals')) {
                return Http::response([
                    'data' => [
                        [
                            'id' => 1,
                            'displayname' => 'Quantainium Station',
                            'name' => 'Admin - Quantainium',
                            'code' => 'QUANT1',
                            'star_system_name' => 'Stanton',
                        ],
                    ],
                ]);
            }

            return Http::response(status: 404);
        });

        $job = new ImportCommodityPrices($version->id);
        $job->handle();

        $prices = $commodity->refresh()->uex_prices;

        expect($prices)->toBeArray()
            ->and($prices)->toHaveCount(1)
            ->and($prices[0])->toMatchArray([
                'terminal_id' => 1,
                'terminal_code' => 'QUANT1',
                'terminal_name' => 'Test Terminal',
                'starmap_location_uuid' => 'bbb22222-3333-4444-5555-666677778889',
                'price_buy' => 500,
                'price_sell' => 250,
                'game_version' => $version->code,
            ]);

        Log::shouldHaveReceived('info')->with('UEX commodity prices imported', [
            'count' => 1,
            'game_version_id' => $version->id,
        ]);
    });

    it('skips prices with zero buy and sell', function (): void {
        Log::spy();

        $version = GameVersion::factory()->create(['code' => '4.8.0-LIVE.1182', 'is_default' => true]);
        $commodity = Commodity::factory()->create(['name' => 'Waste']);

        Http::fake(function ($request) {
            if (str_contains($request->url(), 'commodities_prices_all')) {
                return Http::response([
                    'data' => [
                        [
                            'id_commodity' => 3,
                            'commodity_name' => 'Waste',
                            'id_terminal' => 1,
                            'terminal_name' => 'Junk Terminal',
                            'price_buy' => 0,
                            'price_sell' => 0,
                            'game_version' => '4.8.0-LIVE.1190',
                            'date_modified' => 1700000000,
                        ],
                    ],
                ]);
            }

            if (str_contains($request->url(), 'terminals')) {
                return Http::response(['data' => []]);
            }

            return Http::response(status: 404);
        });

        $job = new ImportCommodityPrices($version->id);
        $job->handle();

        expect($commodity->refresh()->uex_prices)->toBeNull();

        Log::shouldHaveReceived('info')->with('UEX commodity prices imported', [
            'count' => 0,
            'game_version_id' => $version->id,
        ]);
    });

    it('logs API failures and does not update prices', function (): void {
        Log::spy();

        Http::fake([
            'api.uexcorp.uk/*' => Http::response(status: 500),
        ]);

        $version = GameVersion::factory()->create(['code' => '4.8.0-LIVE.1182', 'is_default' => true]);
        $commodity = Commodity::factory()->create(['name' => 'Agricium', 'uex_prices' => [['terminal_name' => 'Old']]]);

        $job = new ImportCommodityPrices($version->id);

        // The job throws so the queue retries it instead of permanently failing.
        expect(fn () => $job->handle())->toThrow(RequestException::class);

        expect($commodity->refresh()->uex_prices)->toBe([['terminal_name' => 'Old']]);

        Log::shouldHaveReceived('error')->with('UEX commodity prices API request failed', [
            'status' => 500,
            'game_version_id' => $version->id,
        ]);
    });

    it('returns early when game version is not found', function (): void {
        Log::spy();

        $job = new ImportCommodityPrices(999999);
        $job->handle();

        Log::shouldHaveReceived('error')->with('Game version not found', ['game_version_id' => 999999]);
    });

    it('imports prices on the retry after a failed api request', function (): void {
        Log::spy();

        $version = GameVersion::factory()->create(['code' => '4.8.0-LIVE.1182', 'is_default' => true]);
        $commodity = Commodity::factory()->create(['name' => 'Laranite']);

        $failedOnce = false;

        Http::fake(function ($request) use (&$failedOnce) {
            if (str_contains($request->url(), 'commodities_prices_all')) {
                if (! $failedOnce) {
                    $failedOnce = true;

                    return Http::response(status: 500);
                }

                return Http::response([
                    'data' => [
                        [
                            'id_commodity' => 1,
                            'commodity_name' => 'Laranite',
                            'id_terminal' => 1,
                            'terminal_name' => 'Test Terminal',
                            'price_buy' => 100,
                            'price_sell' => 50,
                            'date_modified' => 1700000000,
                        ],
                    ],
                ]);
            }

            if (str_contains($request->url(), 'terminals')) {
                return Http::response(['data' => []]);
            }

            return Http::response(['data' => []]);
        });

        $job = new ImportCommodityPrices($version->id);

        // Attempt 1: API fails, the job throws so the queue can retry it.
        expect(fn () => $job->handle())->toThrow(RequestException::class)
            ->and($commodity->refresh()->uex_prices)->toBeNull();

        // Attempt 2 (queue retry): the API recovers and the data lands.
        $job->handle();

        expect($commodity->refresh()->uex_prices)->toBeArray()
            ->and($commodity->uex_prices)->toHaveCount(1)
            ->and($commodity->uex_prices[0])->toMatchArray([
                'terminal_id' => 1,
                'terminal_name' => 'Test Terminal',
                'price_buy' => 100,
                'price_sell' => 50,
                'game_version' => $version->code,
            ]);
    });
});

it('selects numeric commodity dates before missing dates with stable ties', function (): void {
    $version = GameVersion::factory()->create(['code' => '4.7.1-LIVE']);
    $commodity = Commodity::factory()->create(['name' => 'Recency Commodity']);
    $rows = [];
    foreach ([[1, null, 1], [1, 10, 2], [1, 20, 3], [1, 20, 4], [2, null, 5], [2, null, 6], [3, 20, 7], [3, null, 8]] as [$terminal, $date, $price]) {
        $rows[] = ['id_commodity' => 1, 'commodity_name' => 'Recency Commodity', 'id_terminal' => $terminal, 'terminal_name' => 'T', 'price_buy' => $price, 'price_sell' => 0, 'date_modified' => $date];
    }
    Http::fake(fn ($request) => str_contains($request->url(), 'commodities_prices_all') ? Http::response(['data' => $rows]) : Http::response(['data' => []]));
    (new ImportCommodityPrices($version->id))->handle();
    $prices = collect($commodity->refresh()->uex_prices)->keyBy('terminal_id');
    expect($prices[1]['price_buy'])->toBe(3)->and($prices[2]['price_buy'])->toBe(5)->and($prices[3]['price_buy'])->toBe(7);
});

it('matches complete current and previous version families at numeric boundaries', function (): void {
    $version = GameVersion::factory()->create(['code' => '4.10.1-LIVE']);
    $commodity = Commodity::factory()->create(['name' => 'Family Commodity']);
    $rows = [];
    foreach (['4.1.9', '4.10.0', '4.11.0'] as $i => $family) {
        $rows[] = ['id_commodity' => 1, 'commodity_name' => 'Family Commodity', 'id_terminal' => $i + 1, 'terminal_name' => 'T', 'price_buy' => 10, 'price_sell' => 0, 'date_modified' => 10, 'game_version' => $family];
    }
    Http::fake(fn ($request) => str_contains($request->url(), 'commodities_prices_all') ? Http::response(['data' => $rows]) : Http::response(['data' => []]));
    (new ImportCommodityPrices($version->id, '4.1.2-LIVE'))->handle();
    $prices = collect($commodity->refresh()->uex_prices)->keyBy('terminal_id');
    expect($prices->keys()->sort()->values()->all())->toBe([1, 2])
        ->and($prices[1]['game_version'])->toBe('4.1.2-LIVE')
        ->and($prices[2]['game_version'])->toBe('4.10.1-LIVE');
});

it('rejects 4.10 when the current family is 4.1', function (): void {
    $version = GameVersion::factory()->create(['code' => '4.1.2-LIVE']);
    $commodity = Commodity::factory()->create(['name' => 'Boundary Commodity']);
    $rows = [];
    foreach (['4.1.9', '4.10.0'] as $i => $family) {
        $rows[] = ['id_commodity' => 1, 'commodity_name' => 'Boundary Commodity', 'id_terminal' => $i + 1, 'terminal_name' => 'T', 'price_buy' => 10, 'price_sell' => 0, 'date_modified' => 10, 'game_version' => $family];
    }
    Http::fake(fn ($request) => str_contains($request->url(), 'commodities_prices_all') ? Http::response(['data' => $rows]) : Http::response(['data' => []]));
    (new ImportCommodityPrices($version->id))->handle();
    expect(collect($commodity->refresh()->uex_prices)->pluck('terminal_id')->all())->toBe([1]);
});
