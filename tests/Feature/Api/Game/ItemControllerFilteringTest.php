<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\Game\Item;
use App\Models\Game\ItemData;
use App\Models\Game\Manufacturer;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('filters items by type and manufacturer', function (): void {
    $version = GameVersion::factory()->create([
        'code' => '3.24.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $manufacturer = Manufacturer::factory()->create([
        'name' => 'Acme',
        'code' => 'ACME',
    ]);

    $otherManufacturer = Manufacturer::factory()->create([
        'name' => 'Other',
        'code' => 'OTHER',
    ]);

    $match = Item::factory()->create();
    ItemData::factory()
        ->for($match)
        ->for($version, 'gameVersion')
        ->for($manufacturer)
        ->create([
            'name' => 'Widget One',
            'type' => 'Widget',
            'class_name' => 'WidgetOne',
            'classification' => 'Test',
            'data' => [],
        ]);

    $other = Item::factory()->create();
    ItemData::factory()
        ->for($other)
        ->for($version, 'gameVersion')
        ->for($otherManufacturer)
        ->create([
            'name' => 'Gadget One',
            'type' => 'Gadget',
            'class_name' => 'GadgetOne',
            'classification' => 'Test',
            'data' => [],
        ]);

    $response = $this->getJson('/api/items?filter[type]=Widget&filter[manufacturer]=Acme');

    $response->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.uuid', $match->uuid);

    $response = $this->getJson('/api/items?filter[type]=Widget&filter[manufacturer]=ACME');

    $response->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.uuid', $match->uuid);
});

it('filters items by variants flag', function (): void {
    $version = GameVersion::factory()->create([
        'code' => '3.24.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $manufacturer = Manufacturer::factory()->create([
        'name' => 'Variants Co',
        'code' => 'VARIANTS',
    ]);

    $baseItem = Item::factory()->create();
    $baseData = ItemData::factory()
        ->for($baseItem)
        ->for($version, 'gameVersion')
        ->for($manufacturer)
        ->create([
            'name' => 'Base Widget',
            'type' => 'Widget',
            'class_name' => 'WidgetBase',
            'classification' => 'Test',
            'data' => [],
        ]);

    $variantItem = Item::factory()->create();
    ItemData::factory()
        ->for($variantItem)
        ->for($version, 'gameVersion')
        ->for($manufacturer)
        ->create([
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
        ->assertJsonPath('data.0.uuid', $baseItem->uuid);
});

it('filters search results by manufacturer', function (): void {
    $version = GameVersion::factory()->create([
        'code' => '3.24.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $manufacturer = Manufacturer::factory()->create([
        'name' => 'Search Co',
        'code' => 'SEARCH',
    ]);

    $otherManufacturer = Manufacturer::factory()->create([
        'name' => 'Search Other',
        'code' => 'SEARCHO',
    ]);

    $match = Item::factory()->create();
    ItemData::factory()
        ->for($match)
        ->for($version, 'gameVersion')
        ->for($manufacturer)
        ->create([
            'name' => 'Laser Cutter',
            'type' => 'Tool',
            'class_name' => 'LaserCutter',
            'classification' => 'Test',
            'data' => [],
        ]);

    $other = Item::factory()->create();
    ItemData::factory()
        ->for($other)
        ->for($version, 'gameVersion')
        ->for($otherManufacturer)
        ->create([
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
        ->assertJsonPath('data.0.uuid', $match->uuid);

    $response = $this->postJson('/api/items/search', [
        'query' => 'Laser',
        'filter' => [
            'manufacturer' => 'SEARCH',
        ],
    ]);

    $response->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.uuid', $match->uuid);
});

it('ignores unknown filters', function (): void {
    $version = GameVersion::factory()->create([
        'code' => '3.24.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $manufacturer = Manufacturer::factory()->create([
        'name' => 'Unknown Co',
        'code' => 'UNKNOWN',
    ]);

    $item = Item::factory()->create();
    ItemData::factory()
        ->for($item)
        ->for($version, 'gameVersion')
        ->for($manufacturer)
        ->create([
            'name' => 'Unknown Widget',
            'type' => 'Widget',
            'class_name' => 'UnknownWidget',
            'classification' => 'Test',
            'data' => [],
        ]);

    $response = $this->getJson('/api/items?filter[unknown]=value');

    $response->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.uuid', $item->uuid);
});
