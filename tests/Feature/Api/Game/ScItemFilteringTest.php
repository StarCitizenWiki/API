<?php

use App\Models\Game\GameVersion;
use App\Models\Game\Item;
use App\Models\Game\ItemData;
use App\Models\Game\Manufacturer;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('filters items by type and manufacturer', function () {
    $version = GameVersion::create([
        'code' => '3.24.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $manufacturer = Manufacturer::create([
        'uuid' => 'manufacturer-acme',
        'name' => 'Acme',
        'code' => 'ACME',
    ]);

    $otherManufacturer = Manufacturer::create([
        'uuid' => 'manufacturer-other',
        'name' => 'Other',
        'code' => 'OTHER',
    ]);

    $match = Item::create(['uuid' => 'item-match']);
    ItemData::create([
        'item_id' => $match->id,
        'game_version_id' => $version->id,
        'manufacturer_id' => $manufacturer->id,
        'name' => 'Widget One',
        'type' => 'Widget',
        'class_name' => 'WidgetOne',
        'classification' => 'Test',
        'data' => [],
    ]);

    $other = Item::create(['uuid' => 'item-other']);
    ItemData::create([
        'item_id' => $other->id,
        'game_version_id' => $version->id,
        'manufacturer_id' => $otherManufacturer->id,
        'name' => 'Gadget One',
        'type' => 'Gadget',
        'class_name' => 'GadgetOne',
        'classification' => 'Test',
        'data' => [],
    ]);

    $response = $this->getJson('/api/items?filter[type]=Widget&filter[manufacturer]=Acme');

    $response->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.uuid', 'item-match');
});

it('filters items by variants flag', function () {
    $version = GameVersion::create([
        'code' => '3.24.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $manufacturer = Manufacturer::create([
        'uuid' => 'manufacturer-variants',
        'name' => 'Variants Co',
        'code' => 'VARIANTS',
    ]);

    $baseItem = Item::create(['uuid' => 'item-base']);
    $baseData = ItemData::create([
        'item_id' => $baseItem->id,
        'game_version_id' => $version->id,
        'manufacturer_id' => $manufacturer->id,
        'name' => 'Base Widget',
        'type' => 'Widget',
        'class_name' => 'WidgetBase',
        'classification' => 'Test',
        'data' => [],
    ]);

    $variantItem = Item::create(['uuid' => 'item-variant']);
    ItemData::create([
        'item_id' => $variantItem->id,
        'game_version_id' => $version->id,
        'manufacturer_id' => $manufacturer->id,
        'name' => 'Variant Widget',
        'type' => 'Widget',
        'class_name' => 'WidgetVariant',
        'classification' => 'Test',
        'base_id' => $baseData->id,
        'data' => [],
    ]);

    $response = $this->getJson('/api/items?filter[variants]=false');

    $response->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.uuid', 'item-base');
});

it('filters search results by manufacturer', function () {
    $version = GameVersion::create([
        'code' => '3.24.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $manufacturer = Manufacturer::create([
        'uuid' => 'manufacturer-search',
        'name' => 'Search Co',
        'code' => 'SEARCH',
    ]);

    $otherManufacturer = Manufacturer::create([
        'uuid' => 'manufacturer-search-other',
        'name' => 'Search Other',
        'code' => 'SEARCHO',
    ]);

    $match = Item::create(['uuid' => 'item-search-match']);
    ItemData::create([
        'item_id' => $match->id,
        'game_version_id' => $version->id,
        'manufacturer_id' => $manufacturer->id,
        'name' => 'Laser Cutter',
        'type' => 'Tool',
        'class_name' => 'LaserCutter',
        'classification' => 'Test',
        'data' => [],
    ]);

    $other = Item::create(['uuid' => 'item-search-other']);
    ItemData::create([
        'item_id' => $other->id,
        'game_version_id' => $version->id,
        'manufacturer_id' => $otherManufacturer->id,
        'name' => 'Laser Rifle',
        'type' => 'WeaponPersonal',
        'class_name' => 'LaserRifle',
        'classification' => 'Test',
        'data' => [],
    ]);

    $response = $this->postJson('/api/items/search', [
        'query' => 'Laser',
        'filter' => [
            'manufacturer' => 'Search Co',
        ],
    ]);

    $response->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.uuid', 'item-search-match');
});

it('ignores unknown filters', function () {
    $version = GameVersion::create([
        'code' => '3.24.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $manufacturer = Manufacturer::create([
        'uuid' => 'manufacturer-unknown',
        'name' => 'Unknown Co',
        'code' => 'UNKNOWN',
    ]);

    $item = Item::create(['uuid' => 'item-unknown']);
    ItemData::create([
        'item_id' => $item->id,
        'game_version_id' => $version->id,
        'manufacturer_id' => $manufacturer->id,
        'name' => 'Unknown Widget',
        'type' => 'Widget',
        'class_name' => 'UnknownWidget',
        'classification' => 'Test',
        'data' => [],
    ]);

    $response = $this->getJson('/api/items?filter[unknown]=value');

    $response->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.uuid', 'item-unknown');
});
