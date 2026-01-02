<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\Game\Item;
use App\Models\Game\ItemData;
use App\Models\Game\Manufacturer;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->gameVersion = GameVersion::create([
        'code' => '4.0.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $this->manufacturer = Manufacturer::create([
        'uuid' => 'manufacturer-test',
        'name' => 'Test Manufacturer',
        'code' => 'TEST',
    ]);
});

it('shows an item by UUID', function () {
    $uuid = '550e8400-e29b-41d4-a716-446655440000';
    $item = Item::create(['uuid' => $uuid]);

    ItemData::create([
        'item_id' => $item->id,
        'game_version_id' => $this->gameVersion->id,
        'name' => 'Faction Jacket Green',
        'type' => 'Clothing',
        'class_name' => 'cds_armor_heavy_arms_01_02_01',
        'classification' => 'FPS.Clothing.Torso',
        'manufacturer_id' => $this->manufacturer->id,
        'data' => ['stdItem' => []],
    ]);

    $response = $this->getJson("/api/items/{$uuid}");

    $response->assertSuccessful()
        ->assertJsonPath('data.uuid', $uuid)
        ->assertJsonPath('data.name', 'Faction Jacket Green')
        ->assertJsonPath('data.class_name', 'cds_armor_heavy_arms_01_02_01');
});

it('shows an item by exact name', function () {
    $item = Item::create(['uuid' => 'test-item-uuid-456']);

    ItemData::create([
        'item_id' => $item->id,
        'game_version_id' => $this->gameVersion->id,
        'name' => 'Faction Jacket Green',
        'type' => 'Clothing',
        'class_name' => 'cds_armor_heavy_arms_01_02_01',
        'classification' => 'FPS.Clothing.Torso',
        'manufacturer_id' => $this->manufacturer->id,
        'data' => ['stdItem' => []],
    ]);

    $response = $this->getJson('/api/items/Faction Jacket Green');

    $response->assertSuccessful()
        ->assertJsonPath('data.uuid', 'test-item-uuid-456')
        ->assertJsonPath('data.name', 'Faction Jacket Green');
});

it('shows an item by case-insensitive name', function () {
    $item = Item::create(['uuid' => 'test-item-uuid-789']);

    ItemData::create([
        'item_id' => $item->id,
        'game_version_id' => $this->gameVersion->id,
        'name' => 'Faction Jacket Green',
        'type' => 'Clothing',
        'class_name' => 'cds_armor_heavy_arms_01_02_01',
        'classification' => 'FPS.Clothing.Torso',
        'manufacturer_id' => $this->manufacturer->id,
        'data' => ['stdItem' => []],
    ]);

    $response = $this->getJson('/api/items/faction jacket green');

    $response->assertSuccessful()
        ->assertJsonPath('data.uuid', 'test-item-uuid-789')
        ->assertJsonPath('data.name', 'Faction Jacket Green');
});

it('shows an item by exact class_name', function () {
    $item = Item::create(['uuid' => 'test-item-uuid-class']);

    ItemData::create([
        'item_id' => $item->id,
        'game_version_id' => $this->gameVersion->id,
        'name' => 'Heavy Armor Arms',
        'type' => 'Armor',
        'class_name' => 'cds_armor_heavy_arms_01_02_01',
        'classification' => 'FPS.Armor.Arms',
        'manufacturer_id' => $this->manufacturer->id,
        'data' => ['stdItem' => []],
    ]);

    $response = $this->getJson('/api/items/cds_armor_heavy_arms_01_02_01');

    $response->assertSuccessful()
        ->assertJsonPath('data.uuid', 'test-item-uuid-class')
        ->assertJsonPath('data.name', 'Heavy Armor Arms')
        ->assertJsonPath('data.class_name', 'cds_armor_heavy_arms_01_02_01');
});

it('shows an item by case-insensitive class_name', function () {
    $item = Item::create(['uuid' => 'test-item-uuid-class-upper']);

    ItemData::create([
        'item_id' => $item->id,
        'game_version_id' => $this->gameVersion->id,
        'name' => 'Heavy Armor Arms',
        'type' => 'Armor',
        'class_name' => 'cds_armor_heavy_arms_01_02_01',
        'classification' => 'FPS.Armor.Arms',
        'manufacturer_id' => $this->manufacturer->id,
        'data' => ['stdItem' => []],
    ]);

    $response = $this->getJson('/api/items/CDS_ARMOR_HEAVY_ARMS_01_02_01');

    $response->assertSuccessful()
        ->assertJsonPath('data.uuid', 'test-item-uuid-class-upper')
        ->assertJsonPath('data.name', 'Heavy Armor Arms')
        ->assertJsonPath('data.class_name', 'cds_armor_heavy_arms_01_02_01');
});

it('shows an item by class_name with spaces converted to underscores', function () {
    $item = Item::create(['uuid' => 'test-item-uuid-class-spaces']);

    ItemData::create([
        'item_id' => $item->id,
        'game_version_id' => $this->gameVersion->id,
        'name' => 'Heavy Armor Arms',
        'type' => 'Armor',
        'class_name' => 'cds_armor_heavy_arms_01_02_01',
        'classification' => 'FPS.Armor.Arms',
        'manufacturer_id' => $this->manufacturer->id,
        'data' => ['stdItem' => []],
    ]);

    $response = $this->getJson('/api/items/cds armor heavy arms 01 02 01');

    $response->assertSuccessful()
        ->assertJsonPath('data.uuid', 'test-item-uuid-class-spaces')
        ->assertJsonPath('data.name', 'Heavy Armor Arms')
        ->assertJsonPath('data.class_name', 'cds_armor_heavy_arms_01_02_01');
});

it('shows an item by name with underscores converted from spaces', function () {
    $item = Item::create(['uuid' => 'test-item-uuid-underscored']);

    ItemData::create([
        'item_id' => $item->id,
        'game_version_id' => $this->gameVersion->id,
        'name' => 'Faction Jacket Green',
        'type' => 'Clothing',
        'class_name' => 'faction_jacket_green',
        'classification' => 'FPS.Clothing.Torso',
        'manufacturer_id' => $this->manufacturer->id,
        'data' => ['stdItem' => []],
    ]);

    $response = $this->getJson('/api/items/Faction_Jacket_Green');

    $response->assertSuccessful()
        ->assertJsonPath('data.uuid', 'test-item-uuid-underscored')
        ->assertJsonPath('data.name', 'Faction Jacket Green');
});

it('returns not found for non-existent item', function () {
    $response = $this->getJson('/api/items/non-existent-item');

    $response->assertNotFound();
});

it('redirects to vehicle endpoint for vehicle items', function () {
    $uuid = '650e8400-e29b-41d4-a716-446655440000';
    $item = Item::create(['uuid' => $uuid]);

    ItemData::create([
        'item_id' => $item->id,
        'game_version_id' => $this->gameVersion->id,
        'name' => 'Test Vehicle',
        'type' => 'NOITEM_Vehicle',
        'class_name' => 'test_vehicle',
        'classification' => 'Vehicle',
        'manufacturer_id' => $this->manufacturer->id,
        'data' => ['stdItem' => []],
    ]);

    $response = $this->getJson("/api/items/{$uuid}");

    $response->assertRedirect("/api/vehicles/{$uuid}");
});

it('includes related items when requested', function () {
    $baseUuid = '123e4567-e89b-12d3-a456-426614174000';
    $variantUuid = '123e4567-e89b-12d3-a456-426614174001';

    $baseItem = Item::create(['uuid' => $baseUuid]);
    $baseData = ItemData::create([
        'item_id' => $baseItem->id,
        'game_version_id' => $this->gameVersion->id,
        'name' => 'Test Base Item',
        'type' => 'Weapon',
        'class_name' => 'test_base',
        'classification' => 'WeaponPersonal',
        'manufacturer_id' => $this->manufacturer->id,
        'data' => ['stdItem' => []],
    ]);

    $variantItem = Item::create(['uuid' => $variantUuid]);
    ItemData::create([
        'item_id' => $variantItem->id,
        'game_version_id' => $this->gameVersion->id,
        'name' => 'Test Variant Item',
        'type' => 'Weapon',
        'class_name' => 'test_variant',
        'classification' => 'WeaponPersonal',
        'manufacturer_id' => $this->manufacturer->id,
        'base_id' => $baseData->id,
        'data' => ['stdItem' => []],
    ]);

    $response = $this->getJson("/api/items/{$variantUuid}?include=related_items");

    $response->assertSuccessful()
        ->assertJsonPath('data.uuid', $variantUuid)
        ->assertJsonStructure([
            'data' => [
                'related_items' => [
                    'set_name',
                    'base_item',
                    'variant_items',
                    'set_items',
                ],
            ],
        ]);
});

it('does not include related items when not requested', function () {
    $uuid = '223e4567-e89b-12d3-a456-426614174002';

    $item = Item::create(['uuid' => $uuid]);
    ItemData::create([
        'item_id' => $item->id,
        'game_version_id' => $this->gameVersion->id,
        'name' => 'Test Item',
        'type' => 'Weapon',
        'class_name' => 'test_item',
        'classification' => 'WeaponPersonal',
        'manufacturer_id' => $this->manufacturer->id,
        'data' => ['stdItem' => []],
    ]);

    $response = $this->getJson("/api/items/{$uuid}");

    $response->assertSuccessful()
        ->assertJsonMissing(['related_items']);
});
