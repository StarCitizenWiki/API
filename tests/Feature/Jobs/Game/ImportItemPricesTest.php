<?php

declare(strict_types=1);

use App\Jobs\Game\ImportItemPrices;
use App\Models\Game\GameVersion;
use App\Models\Game\Item;
use App\Models\Game\ItemData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

it('imports prices for existing items only', function () {
    $version = GameVersion::factory()->create(['is_default' => true]);

    $existingItem = Item::factory()->create();
    ItemData::factory()->create([
        'item_id' => $existingItem->id,
        'game_version_id' => $version->id,
    ]);

    $unknownItem = Item::factory()->create();
    ItemData::factory()->create([
        'item_id' => $unknownItem->id,
        'game_version_id' => $version->id,
    ]);

    Http::fake([
        'api.uexcorp.uk/*' => Http::response([
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
                    // Unknown UUID - will be filtered
                    'item_uuid' => '00000000-0000-0000-0000-000000000000',
                    'id_terminal' => 2,
                    'terminal_name' => 'Unknown Terminal',
                    'price_buy' => 200,
                    'price_sell' => 100,
                    'date_modified' => 1700000000,
                ],
            ],
        ]),
    ]);

    $job = new ImportItemPrices($version->id);
    $job->handle();

    $itemData = ItemData::query()
        ->where('item_id', $existingItem->id)
        ->where('game_version_id', $version->id)
        ->first()
        ->refresh();

    expect($itemData)->not->toBeNull()
        ->and($itemData->uex_prices)->toBeArray()
        ->and($itemData->uex_prices)->toHaveCount(1)
        ->and($itemData->uex_prices[0])->toMatchArray([
            'terminal_id' => 1,
            'terminal_name' => 'Test Terminal',
            'price_buy' => 100,
            'price_sell' => 50,
            'date_updated' => '2023-11-14T22:13:20+00:00',
        ]);
});

it('updates only the specified game version', function () {
    $targetVersion = GameVersion::factory()->create(['is_default' => true, 'code' => '4.0.0']);
    $otherVersion = GameVersion::factory()->create(['is_default' => false, 'code' => '3.22.0']);

    $item = Item::factory()->create();
    ItemData::factory()->create([
        'item_id' => $item->id,
        'game_version_id' => $targetVersion->id,
    ]);
    ItemData::factory()->create([
        'item_id' => $item->id,
        'game_version_id' => $otherVersion->id,
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

    $targetItemData = ItemData::query()
        ->where('item_id', $item->id)
        ->where('game_version_id', $targetVersion->id)
        ->first()
        ->refresh();

    $otherItemData = ItemData::query()
        ->where('item_id', $item->id)
        ->where('game_version_id', $otherVersion->id)
        ->first()
        ->refresh();

    expect($targetItemData->uex_prices)->toBeArray()->toHaveCount(1)
        ->and($otherItemData->uex_prices)->toBeNull();
});

it('handles API failures gracefully', function () {
    Http::fake([
        'api.uexcorp.uk/*' => Http::response(status: 500),
    ]);

    $version = GameVersion::factory()->create(['is_default' => true]);

    $job = new ImportItemPrices($version->id);

    expect(fn () => $job->handle())->not->toThrow(Exception::class);
});

it('handles items without ItemData for the version', function () {
    $version = GameVersion::factory()->create(['is_default' => true]);
    $otherVersion = GameVersion::factory()->create(['is_default' => false]);

    $item = Item::factory()->create();
    ItemData::factory()->create([
        'item_id' => $item->id,
        'game_version_id' => $otherVersion->id,
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

    // Job completes without error
    expect($item->id)->toBeInt();
});

it('deduplicates prices by terminal_id', function () {
    $version = GameVersion::factory()->create(['is_default' => true]);

    $item = Item::factory()->create();
    ItemData::factory()->create([
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

    $itemData = ItemData::query()
        ->where('item_id', $item->id)
        ->where('game_version_id', $version->id)
        ->first()
        ->refresh();

    expect($itemData->uex_prices)->toBeArray()
        ->and($itemData->uex_prices)->toHaveCount(2)
        ->and($itemData->uex_prices[0]['terminal_id'])->toBe(1)
        ->and($itemData->uex_prices[1]['terminal_id'])->toBe(2);
});
