<?php

declare(strict_types=1);

use App\Jobs\Game\EnrichItemPrices;
use App\Models\Game\GameVersion;
use App\Models\Game\Item;
use App\Models\Game\ItemData;
use App\Models\Game\StarmapLocation;
use App\Models\Game\StarmapLocationData;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

it('enriches item prices from per-item API', function (): void {
    Log::spy();

    $version = GameVersion::factory()->create(['is_default' => true, 'code' => '4.7.1']);

    $locationUuid = 'b1e1e1e1-2222-4333-8444-555566667781';
    $starmapLocation = StarmapLocation::factory()->create(['uuid' => $locationUuid]);
    $starmapLocationData = StarmapLocationData::factory()->create([
        'starmap_location_id' => $starmapLocation->id,
        'game_version_id' => $version->id,
        'name' => 'Area18',
    ]);

    $item = Item::factory()->create();
    $itemData = ItemData::factory()->create([
        'item_id' => $item->id,
        'game_version_id' => $version->id,
        'uex_prices' => [
            [
                'terminal_code' => null,
                'terminal_name' => 'CenterMass - Area18',
                'starmap_location_uuid' => $locationUuid,
                'starmap_location_data_id' => $starmapLocationData->id,
                'price_buy' => 10000,
                'price_sell' => 0,
                'game_version' => '4.7.1',
                'date_updated' => '2024-01-01T00:00:00+00:00',
            ],
        ],
    ]);

    Http::fake(function ($request) {
        if (str_contains($request->url(), 'items_prices?uuid')) {
            return Http::response([
                'data' => [
                    [
                        'id' => 1,
                        'id_terminal' => 107,
                        'terminal_name' => 'CenterMass - IO North Tower - Area 18',
                        'terminal_code' => 'CMA18',
                        'price_buy' => 15461,
                        'price_sell' => 0,
                        'game_version' => '4.7.1',
                        'date_modified' => 1700000000,
                    ],
                ],
            ]);
        }

        if (str_contains($request->url(), 'terminals')) {
            return Http::response([
                'data' => [
                    [
                        'id' => 107,
                        'displayname' => 'Area18',
                        'name' => 'CenterMass - Area 18',
                        'code' => 'CMA18',
                        'star_system_name' => 'Stanton',
                    ],
                ],
            ]);
        }

        return Http::response(status: 404);
    });

    Bus::fake();

    $job = new EnrichItemPrices($version->id, [$item->uuid], [], [], null);
    $job->handle();

    $itemData->refresh();

    expect($itemData->uex_prices)->toBeArray()
        ->and($itemData->uex_prices)->toHaveCount(1)
        ->and($itemData->uex_prices[0])->toMatchArray([
            'terminal_id' => 107,
            'terminal_code' => 'CMA18',
            'terminal_name' => 'CenterMass - IO North Tower - Area 18',
            'starmap_location_uuid' => $locationUuid,
            'starmap_location_data_id' => $starmapLocationData->id,
            'price_buy' => 15461,
            'price_sell' => 0,
            'game_version' => '4.7.1',
            'date_updated' => '2023-11-14T22:13:20+00:00',
        ]);

    Log::shouldHaveReceived('info')->with('UEX item prices enrichment chunk completed', [
        'count' => 1,
        'chunk_size' => 1,
        'game_version_id' => $version->id,
    ]);
});

it('matches current-family prices with major.minor prefix and previous-family with exact patch', function (): void {
    Log::spy();

    $version = GameVersion::factory()->create(['is_default' => true, 'code' => '4.8.0-LIVE.11875683']);
    $previousVersionCode = '4.7.2-LIVE.11674325';

    $locationUuid = 'b1e1e1e1-2222-4333-8444-555566667782';
    $starmapLocation = StarmapLocation::factory()->create(['uuid' => $locationUuid]);
    $starmapLocationData = StarmapLocationData::factory()->create([
        'starmap_location_id' => $starmapLocation->id,
        'game_version_id' => $version->id,
        'name' => 'Area18',
    ]);

    $item = Item::factory()->create();
    $itemData = ItemData::factory()->create([
        'item_id' => $item->id,
        'game_version_id' => $version->id,
        'uex_prices' => [
            [
                'terminal_code' => null,
                'terminal_name' => 'CenterMass - Area18',
                'starmap_location_uuid' => $locationUuid,
                'starmap_location_data_id' => $starmapLocationData->id,
                'price_buy' => 10000,
                'price_sell' => 0,
                'game_version' => $version->code,
                'date_updated' => '2024-01-01T00:00:00+00:00',
            ],
        ],
    ]);

    Http::fake(function ($request) {
        if (str_contains($request->url(), 'items_prices?uuid')) {
            return Http::response([
                'data' => [
                    [
                        'id' => 1,
                        'id_terminal' => 107,
                        'terminal_name' => 'CenterMass - IO North Tower - Area 18',
                        'terminal_code' => 'CMA18',
                        'price_buy' => 15461,
                        'price_sell' => 0,
                        'game_version' => '4.8.0',
                        'date_modified' => 1700000000,
                    ],
                    [
                        'id' => 2,
                        'id_terminal' => 108,
                        'terminal_name' => 'CenterMass - New Babbage',
                        'terminal_code' => 'CMNEW',
                        'price_buy' => 14000,
                        'price_sell' => 0,
                        'game_version' => '4.7.2',
                        'date_modified' => 1700001000,
                    ],
                    [
                        'id' => 3,
                        'id_terminal' => 109,
                        'terminal_name' => 'Grim HEX Weapons',
                        'terminal_code' => 'GHWEAP',
                        'price_buy' => 13000,
                        'price_sell' => 0,
                        'game_version' => '4.7.1',
                        'date_modified' => 1700002000,
                    ],
                ],
            ]);
        }

        if (str_contains($request->url(), 'terminals')) {
            return Http::response([
                'data' => [
                    [
                        'id' => 107,
                        'displayname' => 'Area18',
                        'name' => 'CenterMass - Area 18',
                        'code' => 'CMA18',
                        'star_system_name' => 'Stanton',
                    ],
                ],
            ]);
        }

        return Http::response(status: 404);
    });

    Bus::fake();

    $job = new EnrichItemPrices($version->id, [$item->uuid], [], [], $previousVersionCode);
    $job->handle();

    $itemData->refresh();

    // Should keep 4.8.0 (current family) and 4.7.2 (previous patch)
    // Should drop 4.7.1 (wrong previous patch)
    expect($itemData->uex_prices)->toBeArray()
        ->and($itemData->uex_prices)->toHaveCount(2)
        ->and($itemData->uex_prices[0])->toMatchArray([
            'price_buy' => 15461,
            'game_version' => $version->code,
        ])
        ->and($itemData->uex_prices[1])->toMatchArray([
            'price_buy' => 14000,
            'game_version' => $previousVersionCode,
        ]);
});

it('skips items without existing prices', function (): void {
    Log::spy();

    $version = GameVersion::factory()->create(['is_default' => true]);

    $item = Item::factory()->create();
    ItemData::factory()->create([
        'item_id' => $item->id,
        'game_version_id' => $version->id,
        'uex_prices' => null,
    ]);

    Http::fake([
        'api.uexcorp.uk/*' => Http::response(['data' => []]),
    ]);

    $job = new EnrichItemPrices($version->id, [$item->uuid], [], [], null);
    $job->handle();

    Log::shouldHaveReceived('info')->with('UEX item prices enrichment chunk completed', [
        'count' => 0,
        'chunk_size' => 1,
        'game_version_id' => $version->id,
    ]);
});

it('handles API failures gracefully', function (): void {
    Log::spy();

    $version = GameVersion::factory()->create(['is_default' => true]);

    $item = Item::factory()->create();
    ItemData::factory()->create([
        'item_id' => $item->id,
        'game_version_id' => $version->id,
        'uex_prices' => [
            [
                'terminal_code' => 'OLD',
                'terminal_name' => 'Old Terminal',
                'starmap_location_uuid' => null,
                'starmap_location_data_id' => null,
                'price_buy' => 500,
                'price_sell' => 250,
                'date_updated' => '2024-01-01T00:00:00+00:00',
            ],
        ],
    ]);

    Http::fake([
        'api.uexcorp.uk/*' => Http::response(status: 500),
    ]);

    $job = new EnrichItemPrices($version->id, [$item->uuid], [], [], null);
    $job->handle();

    Log::shouldHaveReceived('warning')->with('UEX per-item price API failed', [
        'uuid' => $item->uuid,
        'status' => 500,
    ]);
});

it('processes multiple UUIDs in a single chunk', function (): void {
    Log::spy();

    $version = GameVersion::factory()->create(['is_default' => true, 'code' => '4.7.1']);

    StarmapLocation::factory()->create(['uuid' => 'b1e1e1e1-2222-4333-8444-555566667782']);
    StarmapLocationData::factory()->create([
        'starmap_location_id' => StarmapLocation::factory()->create(['uuid' => 'b1e1e1e1-2222-4333-8444-555566667783'])->id,
        'game_version_id' => $version->id,
        'name' => 'Multi Terminal',
    ]);

    $item1 = Item::factory()->create();
    $item2 = Item::factory()->create();

    ItemData::factory()->create([
        'item_id' => $item1->id,
        'game_version_id' => $version->id,
        'uex_prices' => [['terminal_code' => null, 'terminal_name' => 'T1', 'starmap_location_uuid' => null, 'starmap_location_data_id' => null, 'price_buy' => 100, 'price_sell' => 50, 'game_version' => '4.7.1', 'date_updated' => '2024-01-01T00:00:00+00:00']],
    ]);

    ItemData::factory()->create([
        'item_id' => $item2->id,
        'game_version_id' => $version->id,
        'uex_prices' => [['terminal_code' => null, 'terminal_name' => 'T2', 'starmap_location_uuid' => null, 'starmap_location_data_id' => null, 'price_buy' => 200, 'price_sell' => 100, 'game_version' => '4.7.1', 'date_updated' => '2024-01-01T00:00:00+00:00']],
    ]);

    $callCount = 0;

    Http::fake(function ($request) use (&$callCount, $item1, $item2) {
        if (str_contains($request->url(), 'items_prices?uuid')) {
            $callCount++;

            $uuid = $request['uuid'] ?? '';

            $data = [];
            if ($uuid === $item1->uuid) {
                $data = [[
                    'id' => 1,
                    'id_terminal' => 10,
                    'terminal_name' => 'Terminal A',
                    'terminal_code' => 'TA',
                    'price_buy' => 111,
                    'price_sell' => 11,
                    'game_version' => '4.7.1',
                    'date_modified' => 1700000000,
                ]];
            } elseif ($uuid === $item2->uuid) {
                $data = [[
                    'id' => 2,
                    'id_terminal' => 20,
                    'terminal_name' => 'Terminal B',
                    'terminal_code' => 'TB',
                    'price_buy' => 222,
                    'price_sell' => 22,
                    'game_version' => '4.7.1',
                    'date_modified' => 1700000100,
                ]];
            }

            return Http::response(['data' => $data]);
        }

        if (str_contains($request->url(), 'terminals')) {
            return Http::response([
                'data' => [
                    [
                        'id' => 10,
                        'displayname' => 'Multi Terminal',
                        'name' => 'Terminal A',
                        'code' => 'TA',
                        'star_system_name' => 'Stanton',
                    ],
                    [
                        'id' => 20,
                        'displayname' => 'Multi Terminal',
                        'name' => 'Terminal B',
                        'code' => 'TB',
                        'star_system_name' => 'Stanton',
                    ],
                ],
            ]);
        }

        return Http::response(status: 404);
    });

    $job = new EnrichItemPrices($version->id, [$item1->uuid, $item2->uuid], [], [], null);
    $job->handle();

    expect($callCount)->toBe(2);

    Log::shouldHaveReceived('info')->with('UEX item prices enrichment chunk completed', [
        'count' => 2,
        'chunk_size' => 2,
        'game_version_id' => $version->id,
    ]);
});

it('skips blank UUID overrides so UEX does not return all item prices', function (): void {
    Log::spy();

    $version = GameVersion::factory()->create(['is_default' => true, 'code' => '4.7.1']);

    $wikiUuid = 'b616b3ad-123c-40f2-80bd-b8f4109633aa';
    $item = Item::factory()->create(['uuid' => $wikiUuid]);
    $itemData = ItemData::factory()->create([
        'item_id' => $item->id,
        'game_version_id' => $version->id,
        'uex_prices' => [
            [
                'terminal_id' => 149,
                'terminal_code' => 'NDLOR',
                'terminal_name' => 'New Deal Lorville',
                'starmap_location_uuid' => null,
                'starmap_location_data_id' => null,
                'price_buy' => 1005480,
                'price_sell' => 0,
                'game_version' => '4.7.1',
                'date_updated' => '2024-01-01T00:00:00+00:00',
            ],
        ],
    ]);

    Http::fake(function ($request) {
        $url = $request->url();

        if (str_contains($url, 'terminals')) {
            return Http::response(['data' => []]);
        }

        if (str_contains($url, 'items_prices')) {
            return Http::response([
                'data' => [
                    [
                        'id' => 1,
                        'id_terminal' => 149,
                        'terminal_name' => 'New Deal - Teasa Spaceport - Lorville',
                        'terminal_code' => 'NDLOR',
                        'price_buy' => 34466600,
                        'price_sell' => 0,
                        'game_version' => '4.7.1',
                        'date_modified' => 1700000000,
                    ],
                ],
            ]);
        }

        return Http::response(status: 404);
    });

    // Empty UUID in map, no ID map, item should be skipped entirely
    $job = new EnrichItemPrices($version->id, [$wikiUuid], [$wikiUuid => ''], []);
    $job->handle();

    $itemData->refresh();

    expect($itemData->uex_prices)->toHaveCount(1)
        ->and($itemData->uex_prices[0]['price_buy'])->toBe(1005480);

    Log::shouldHaveReceived('info')->with('UEX item prices enrichment chunk completed', [
        'count' => 0,
        'chunk_size' => 1,
        'game_version_id' => $version->id,
    ]);
});

it('enriches item prices using id_item fallback for empty-UUID items', function (): void {
    Log::spy();

    $version = GameVersion::factory()->create(['is_default' => true, 'code' => '4.7.1']);

    $wikiUuid = 'b616b3ad-123c-40f2-80bd-b8f4109633aa';
    $uexId = 987;
    $item = Item::factory()->create(['uuid' => $wikiUuid]);
    $itemData = ItemData::factory()->create([
        'item_id' => $item->id,
        'game_version_id' => $version->id,
        'uex_prices' => [
            [
                'terminal_id' => 149,
                'terminal_code' => 'NDLOR',
                'terminal_name' => 'New Deal Lorville',
                'starmap_location_uuid' => null,
                'starmap_location_data_id' => null,
                'price_buy' => 1005480,
                'price_sell' => 0,
                'game_version' => '4.7.1',
                'date_updated' => '2024-01-01T00:00:00+00:00',
            ],
        ],
    ]);

    $capturedQueryParams = [];

    Http::fake(function ($request) use (&$capturedQueryParams) {
        $url = $request->url();

        if (str_contains($url, 'items_prices')) {
            parse_str(parse_url($url, PHP_URL_QUERY) ?: '', $params);
            $capturedQueryParams = $params;

            return Http::response([
                'data' => [
                    [
                        'id' => 1,
                        'id_terminal' => 149,
                        'terminal_name' => 'New Deal - Teasa Spaceport - Lorville',
                        'terminal_code' => 'NDLOR',
                        'price_buy' => 1005480,
                        'price_sell' => 0,
                        'game_version' => '4.7.1',
                        'date_modified' => 1700000000,
                    ],
                ],
            ]);
        }

        return Http::response(status: 404);
    });

    // Empty UUID in map, but provide UEX id for fallback
    $job = new EnrichItemPrices($version->id, [$wikiUuid], [$wikiUuid => ''], [$wikiUuid => $uexId]);
    $job->handle();

    // Verify id_item was used, not uuid
    expect($capturedQueryParams)->toHaveKey('id_item')
        ->and($capturedQueryParams['id_item'])->toBe('987')
        ->and($capturedQueryParams)->not->toHaveKey('uuid');

    $itemData->refresh();

    expect($itemData->uex_prices)->toHaveCount(1)
        ->and($itemData->uex_prices[0]['price_buy'])->toBe(1005480);

    Log::shouldHaveReceived('info')->with('UEX item prices enrichment chunk completed', [
        'count' => 1,
        'chunk_size' => 1,
        'game_version_id' => $version->id,
    ]);
});

it('uses reverse UUID override for per-item API calls', function (): void {
    Log::spy();

    $version = GameVersion::factory()->create(['is_default' => true, 'code' => '4.7.1']);

    StarmapLocation::factory()->create(['uuid' => 'b1e1e1e1-2222-4333-8444-555566667784']);
    StarmapLocationData::factory()->create([
        'starmap_location_id' => StarmapLocation::factory()->create()->id,
        'game_version_id' => $version->id,
        'name' => 'Override Terminal',
    ]);

    $wikiItem = Item::factory()->create(['uuid' => '02d4cd2e-fa98-4086-aee1-6b2dfce8ea27']);
    $wikiItemData = ItemData::factory()->create([
        'item_id' => $wikiItem->id,
        'game_version_id' => $version->id,
        'uex_prices' => [
            [
                'terminal_code' => null,
                'terminal_name' => 'Old Terminal',
                'starmap_location_uuid' => null,
                'starmap_location_data_id' => null,
                'price_buy' => 100,
                'price_sell' => 50,
                'game_version' => '4.7.1',
                'date_updated' => '2024-01-01T00:00:00+00:00',
            ],
        ],
    ]);

    $requestedUuid = null;

    Http::fake(function ($request) use (&$requestedUuid) {
        if (str_contains($request->url(), 'items_prices?uuid')) {
            $requestedUuid = $request['uuid'];

            return Http::response([
                'data' => [
                    [
                        'id' => 1,
                        'id_terminal' => 50,
                        'terminal_name' => 'Enriched Terminal',
                        'terminal_code' => 'ENR',
                        'price_buy' => 45000,
                        'price_sell' => 22000,
                        'game_version' => '4.7.1',
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

    config(['uexcorp.item_uuid_overrides' => [
        '5d6c1c28-1589-4c72-8cc3-ff90f998dca3' => '02d4cd2e-fa98-4086-aee1-6b2dfce8ea27',
    ]]);

    $job = new EnrichItemPrices($version->id, ['02d4cd2e-fa98-4086-aee1-6b2dfce8ea27'], [], [], null);
    $job->handle();

    expect($requestedUuid)->toBe('5d6c1c28-1589-4c72-8cc3-ff90f998dca3');

    $wikiItemData->refresh();

    expect($wikiItemData->uex_prices)->toBeArray()
        ->and($wikiItemData->uex_prices)->toHaveCount(1)
        ->and($wikiItemData->uex_prices[0])->toMatchArray([
            'terminal_code' => 'ENR',
            'terminal_name' => 'Enriched Terminal',
            'price_buy' => 45000,
            'price_sell' => 22000,
            'game_version' => '4.7.1',
        ]);
});
