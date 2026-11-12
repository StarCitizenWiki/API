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

        if (str_ends_with($request->url(), '/terminals')) {
            return Http::response(['data' => []]);
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

it('fails without changing commodity prices when terminal mapping fails', function (): void {
    $version = GameVersion::factory()->create(['code' => '4.7.1']);
    $prices = [[
        'uex_commodity_id' => 8,
        'terminal_id' => 1,
        'terminal_code' => 'OLD',
        'starmap_location_uuid' => 'b1b11111-2222-3333-4444-555566667787',
        'price_buy' => 100,
    ]];
    $commodity = Commodity::factory()->create(['uex_prices' => $prices]);

    Http::fake([
        '*/terminals' => Http::response([], 500),
        '*/commodities_prices*' => Http::response(['data' => [[
            'id_terminal' => 1, 'terminal_name' => 'Terminal', 'price_buy' => 200,
            'price_sell' => 50, 'game_version' => '4.7.1', 'date_modified' => 1700000000,
        ]]]),
    ]);

    $exception = null;
    try {
        (new EnrichCommodityPrices($version->id, [$commodity->id]))->handle();
    } catch (\Illuminate\Http\Client\RequestException $caught) {
        $exception = $caught;
    }

    expect($exception)->not->toBeNull()
        ->and($exception?->response->status())->toBe(500)
        ->and($commodity->refresh()->uex_prices)->toEqual($prices);
});

it('matches complete current and previous commodity families at enrichment boundaries', function (): void {
    $version = GameVersion::factory()->create(['code' => '4.10.1-LIVE']);
    $commodity = Commodity::factory()->create(['name' => 'Family Commodity', 'uex_prices' => [['uex_commodity_id' => 8]]]);
    $rows = [];
    foreach (['4.1.9', '4.10.0', '4.11.0'] as $i => $family) {
        $rows[] = ['id_terminal' => $i + 1, 'terminal_name' => 'T', 'price_buy' => 10, 'price_sell' => 0, 'date_modified' => 10, 'game_version' => $family];
    }
    Http::fake(fn ($request) => str_contains($request->url(), 'commodities_prices?') ? Http::response(['data' => $rows]) : Http::response(['data' => []]));
    (new EnrichCommodityPrices($version->id, [$commodity->id], '4.1.2-LIVE'))->handle();
    $prices = collect($commodity->refresh()->uex_prices)->keyBy('terminal_id');
    expect($prices->keys()->sort()->values()->all())->toBe([1, 2])
        ->and($prices[1]['game_version'])->toBe('4.1.2-LIVE')
        ->and($prices[2]['game_version'])->toBe('4.10.1-LIVE');
});

it('rejects 4.10 commodity enrichment rows when the current family is 4.1', function (): void {
    $version = GameVersion::factory()->create(['code' => '4.1.2-LIVE']);
    $commodity = Commodity::factory()->create(['name' => 'Boundary Commodity', 'uex_prices' => [['uex_commodity_id' => 8]]]);
    $rows = [];
    foreach (['4.1.9', '4.10.0'] as $i => $family) {
        $rows[] = ['id_terminal' => $i + 1, 'terminal_name' => 'T', 'price_buy' => 10, 'price_sell' => 0, 'date_modified' => 10, 'game_version' => $family];
    }
    Http::fake(fn ($request) => str_contains($request->url(), 'commodities_prices?') ? Http::response(['data' => $rows]) : Http::response(['data' => []]));
    (new EnrichCommodityPrices($version->id, [$commodity->id]))->handle();
    expect(collect($commodity->refresh()->uex_prices)->pluck('terminal_id')->all())->toBe([1])
        ->and($commodity->uex_prices[0]['game_version'])->toBe('4.1.2-LIVE');
});
