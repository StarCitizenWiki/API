<?php

declare(strict_types=1);

use App\Jobs\Game\ImportItemPrices;
use App\Models\Game\GameVersion;
use App\Models\Game\Item;
use App\Models\Game\ItemData;
use App\Models\Game\StarmapLocation;
use App\Models\Game\StarmapLocationData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

uses(RefreshDatabase::class);

it('imports prices for existing items only', function (): void {
    Log::spy();

    $version = GameVersion::factory()->create(['is_default' => true]);

    $existingItem = Item::factory()->create();
    $existingItemData = ItemData::factory()->create([
        'item_id' => $existingItem->id,
        'game_version_id' => $version->id,
    ]);

    $unknownItem = Item::factory()->create();
    $unknownItemData = ItemData::factory()->create([
        'item_id' => $unknownItem->id,
        'game_version_id' => $version->id,
    ]);

    $starmapLocation = StarmapLocation::factory()->create(['uuid' => 'aaa11111-2222-3333-4444-555566667777']);
    $starmapLocationData = StarmapLocationData::factory()->create([
        'starmap_location_id' => $starmapLocation->id,
        'game_version_id' => $version->id,
        'name' => 'Test Terminal Station',
    ]);

    Http::fake(function ($request) use ($existingItem) {
        if (str_contains($request->url(), 'items_prices_all')) {
            return Http::response([
                'data' => [
                    [
                        'item_uuid' => $existingItem->uuid,
                        'id_terminal' => 1,
                        'terminal_name' => 'Test Terminal',
                        'price_buy' => 100,
                        'price_sell' => 50,
                        'date_modified' => 1700000000,
                    ],
                    [
                        'item_uuid' => '00000000-0000-0000-0000-000000000000',
                        'id_terminal' => 2,
                        'terminal_name' => 'Unknown Terminal',
                        'price_buy' => 200,
                        'price_sell' => 100,
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

    $job = new ImportItemPrices($version->id);
    $job->handle();

    $itemData = $existingItemData->refresh();

    expect($itemData->uex_prices)->toBeArray()
        ->and($itemData->uex_prices)->toHaveCount(1)
        ->and($itemData->uex_prices[0])->toMatchArray([
            'terminal_id' => 1,
            'terminal_code' => 'TEST1',
            'terminal_name' => 'Test Terminal',
            'starmap_location_uuid' => 'aaa11111-2222-3333-4444-555566667777',
            'starmap_location_data_id' => $starmapLocationData->id,
            'price_buy' => 100,
            'price_sell' => 50,
            'game_version' => $version->code,
            'date_updated' => '2023-11-14T22:13:20+00:00',
        ])
        ->and($unknownItemData->refresh()->uex_prices)->toBeNull();

    Log::shouldHaveReceived('info')->with('UEX prices imported', [
        'count' => 1,
        'game_version_id' => $version->id,
    ]);
});

it('updates only the specified game version', function (): void {
    Log::spy();

    $targetVersion = GameVersion::factory()->create(['is_default' => true, 'code' => '4.0.0']);
    $otherVersion = GameVersion::factory()->create(['is_default' => false, 'code' => '3.22.0']);

    $item = Item::factory()->create();
    $targetItemData = ItemData::factory()->create([
        'item_id' => $item->id,
        'game_version_id' => $targetVersion->id,
    ]);
    $otherItemData = ItemData::factory()->create([
        'item_id' => $item->id,
        'game_version_id' => $otherVersion->id,
    ]);

    StarmapLocation::factory()->create(['uuid' => 'c1c1c1c1-2222-4333-8444-555566667781']);
    StarmapLocationData::factory()->create([
        'starmap_location_id' => StarmapLocation::factory()->create()->id,
        'game_version_id' => $targetVersion->id,
        'name' => 'Some Station',
    ]);

    Http::fake([
        'api.uexcorp.uk/*' => Http::response([
            'data' => [
                [
                    'item_uuid' => $item->uuid,
                    'id_terminal' => 1,
                    'terminal_name' => 'Test Terminal',
                    'price_buy' => 100,
                    'price_sell' => 50,
                    'date_modified' => 1700000000,
                ],
            ],
        ]),
    ]);

    $job = new ImportItemPrices($targetVersion->id);
    $job->handle();

    expect($targetItemData->refresh()->uex_prices)->toBeArray()->toHaveCount(1)
        ->and($targetItemData->refresh()->uex_prices[0]['game_version'])->toBe('4.0.0')
        ->and($otherItemData->refresh()->uex_prices)->toBeNull();

    Log::shouldHaveReceived('info')->with('UEX prices imported', [
        'count' => 1,
        'game_version_id' => $targetVersion->id,
    ]);
});

it('applies item UUID overrides from config', function (): void {
    Log::spy();

    $version = GameVersion::factory()->create(['is_default' => true]);

    $wikiItem = Item::factory()->create(['uuid' => '02d4cd2e-fa98-4086-aee1-6b2dfce8ea27']);
    $wikiItemData = ItemData::factory()->create([
        'item_id' => $wikiItem->id,
        'game_version_id' => $version->id,
    ]);

    Http::fake([
        'api.uexcorp.uk/*' => Http::response([
            'data' => [
                [
                    'item_uuid' => '5d6c1c28-1589-4c72-8cc3-ff90f998dca3',
                    'id_terminal' => 1,
                    'terminal_name' => 'Test Terminal',
                    'price_buy' => 45000,
                    'price_sell' => 22000,
                    'date_modified' => 1700000000,
                ],
            ],
        ]),
    ]);

    config(['uexcorp.item_uuid_overrides' => [
        '5d6c1c28-1589-4c72-8cc3-ff90f998dca3' => '02d4cd2e-fa98-4086-aee1-6b2dfce8ea27',
    ]]);

    $job = new ImportItemPrices($version->id);
    $job->handle();

    $itemData = $wikiItemData->refresh();

    expect($itemData->uex_prices)->toBeArray()
        ->and($itemData->uex_prices)->toHaveCount(1)
        ->and($itemData->uex_prices[0])->toMatchArray([
            'terminal_name' => 'Test Terminal',
            'price_buy' => 45000,
            'price_sell' => 22000,
            'game_version' => $version->code,
        ]);

    Log::shouldHaveReceived('info')->with('UEX prices imported', [
        'count' => 1,
        'game_version_id' => $version->id,
    ]);
});

it('logs api failures and leaves existing prices untouched', function (): void {
    Log::spy();

    Http::fake([
        'api.uexcorp.uk/*' => Http::response(status: 500),
    ]);

    $version = GameVersion::factory()->create(['is_default' => true]);
    $item = Item::factory()->create();
    $itemData = ItemData::factory()->create([
        'item_id' => $item->id,
        'game_version_id' => $version->id,
        'uex_prices' => [
            [
                'terminal_id' => 1,
                'terminal_code' => 'EXIST',
                'terminal_name' => 'Existing Terminal',
                'starmap_location_uuid' => null,
                'starmap_location_data_id' => null,
                'price_buy' => 900,
                'price_sell' => 450,
                'date_updated' => '2024-01-01T00:00:00+00:00',
            ],
        ],
    ]);

    $job = new ImportItemPrices($version->id);
    $job->handle();

    $prices = $itemData->refresh()->uex_prices;

    expect($prices)->toBeArray()
        ->and($prices)->toHaveCount(1)
        ->and($prices[0])->toMatchArray([
            'terminal_code' => 'EXIST',
            'terminal_name' => 'Existing Terminal',
            'starmap_location_uuid' => null,
            'starmap_location_data_id' => null,
            'price_buy' => 900,
            'price_sell' => 450,
            'date_updated' => '2024-01-01T00:00:00+00:00',
        ]);

    Log::shouldHaveReceived('error')->with('UEX API request failed', [
        'status' => 500,
        'game_version_id' => $version->id,
    ]);
});

it('logs when item data is missing for the requested game version', function (): void {
    Log::spy();

    $version = GameVersion::factory()->create(['is_default' => true]);
    $item = Item::factory()->create();
    ItemData::factory()->create([
        'item_id' => $item->id,
        'game_version_id' => GameVersion::factory()->create(['is_default' => false])->id,
    ]);

    Http::fake([
        'api.uexcorp.uk/*' => Http::response([
            'data' => [
                [
                    'item_uuid' => $item->uuid,
                    'id_terminal' => 1,
                    'terminal_name' => 'Test Terminal',
                    'price_buy' => 100,
                    'price_sell' => 50,
                    'date_modified' => 1700000000,
                ],
            ],
        ]),
    ]);

    $job = new ImportItemPrices($version->id);
    $job->handle();

    expect(ItemData::query()
        ->where('item_id', $item->id)
        ->where('game_version_id', $version->id)
        ->exists())->toBeFalse();

    Log::shouldHaveReceived('debug')->with('ItemData not found for item', [
        'item_id' => $item->id,
        'game_version_id' => $version->id,
    ]);

    Log::shouldHaveReceived('info')->with('UEX prices imported', [
        'count' => 0,
        'game_version_id' => $version->id,
    ]);
});

it('deduplicates prices by terminal_id', function (): void {
    Log::spy();

    $version = GameVersion::factory()->create(['is_default' => true]);

    $item = Item::factory()->create();
    $itemData = ItemData::factory()->create([
        'item_id' => $item->id,
        'game_version_id' => $version->id,
    ]);

    Http::fake([
        'api.uexcorp.uk/*' => Http::response([
            'data' => [
                [
                    'item_uuid' => $item->uuid,
                    'id_terminal' => 1,
                    'terminal_name' => 'Test Terminal',
                    'price_buy' => 100,
                    'price_sell' => 50,
                    'date_modified' => 1700000000,
                ],
                [
                    'item_uuid' => $item->uuid,
                    'id_terminal' => 1,
                    'terminal_name' => 'Test Terminal Duplicate',
                    'price_buy' => 200,
                    'price_sell' => 100,
                    'date_modified' => 1700000100,
                ],
                [
                    'item_uuid' => $item->uuid,
                    'id_terminal' => 2,
                    'terminal_name' => 'Another Terminal',
                    'price_buy' => 300,
                    'price_sell' => 150,
                    'date_modified' => 1700000200,
                ],
            ],
        ]),
    ]);

    $job = new ImportItemPrices($version->id);
    $job->handle();

    expect($itemData->refresh()->uex_prices)->toBeArray()
        ->and($itemData->uex_prices)->toHaveCount(2)
        ->and($itemData->uex_prices[0]['terminal_code'])->toBeNull()
        ->and($itemData->uex_prices[0]['game_version'])->toBe($version->code)
        ->and($itemData->uex_prices[1]['terminal_code'])->toBeNull()
        ->and($itemData->uex_prices[1]['game_version'])->toBe($version->code);

    Log::shouldHaveReceived('info')->with('UEX prices imported', [
        'count' => 1,
        'game_version_id' => $version->id,
    ]);
});
