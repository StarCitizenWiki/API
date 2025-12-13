<?php

use App\Models\Game\GameVersion;
use App\Models\Game\Item;
use App\Models\Game\ItemData;
use App\Models\Game\Manufacturer;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('resolves default game version when no version parameter provided', function () {
    // Create a default game version
    $defaultVersion = GameVersion::create([
        'code' => '3.21.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    // Create a manufacturer
    $manufacturer = Manufacturer::create([
        'uuid' => 'test-manufacturer-uuid',
        'name' => 'Test Manufacturer',
        'code' => 'TEST',
    ]);

    // Create an item with data for the default version
    $item = Item::create(['uuid' => 'test-item-uuid']);
    ItemData::create([
        'item_id' => $item->id,
        'game_version_id' => $defaultVersion->id,
        'manufacturer_id' => $manufacturer->id,
        'name' => 'Default Version Item',
        'class_name' => 'TestItem',
        'classification' => 'TestClass',
        'data' => [],
    ]);

    $response = $this->getJson("/api/items/{$item->uuid}");

    $response->assertSuccessful()
        ->assertJsonPath('data.name', 'Default Version Item')
        ->assertJsonPath('data.version', '3.21.0-LIVE');
});

it('resolves specific game version from version parameter', function () {
    // Create multiple versions
    $oldVersion = GameVersion::create([
        'code' => '3.20.0-LIVE',
        'channel' => 'live',
        'is_default' => false,
        'released_at' => now()->subDays(7),
    ]);

    $newVersion = GameVersion::create([
        'code' => '3.21.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    // Create a manufacturer
    $manufacturer = Manufacturer::create([
        'uuid' => 'test-manufacturer-uuid-2',
        'name' => 'Test Manufacturer',
        'code' => 'TEST',
    ]);

    // Create an item with data for both versions
    $item = Item::create(['uuid' => 'test-item-uuid-2']);

    ItemData::create([
        'item_id' => $item->id,
        'game_version_id' => $oldVersion->id,
        'manufacturer_id' => $manufacturer->id,
        'name' => 'Old Version Item',
        'class_name' => 'TestItem',
        'classification' => 'TestClass',
        'data' => [],
    ]);

    ItemData::create([
        'item_id' => $item->id,
        'game_version_id' => $newVersion->id,
        'manufacturer_id' => $manufacturer->id,
        'name' => 'New Version Item',
        'class_name' => 'TestItem',
        'classification' => 'TestClass',
        'data' => [],
    ]);

    // Request the old version specifically
    $response = $this->getJson("/api/items/{$item->uuid}?version=3.20.0-LIVE");

    $response->assertSuccessful()
        ->assertJsonPath('data.name', 'Old Version Item')
        ->assertJsonPath('data.version', '3.20.0-LIVE');

    // Request the new version specifically
    $response = $this->getJson("/api/items/{$item->uuid}?version=3.21.0-LIVE");

    $response->assertSuccessful()
        ->assertJsonPath('data.name', 'New Version Item')
        ->assertJsonPath('data.version', '3.21.0-LIVE');
});

it('loads equipped items with correct game version', function () {
    $version = GameVersion::create([
        'code' => '3.21.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    // Create a manufacturer
    $manufacturer = Manufacturer::create([
        'uuid' => 'test-manufacturer-uuid-3',
        'name' => 'Test Manufacturer',
        'code' => 'TEST',
    ]);

    // Create main item with port data
    $mainItem = Item::create(['uuid' => 'main-item-uuid']);
    ItemData::create([
        'item_id' => $mainItem->id,
        'game_version_id' => $version->id,
        'manufacturer_id' => $manufacturer->id,
        'name' => 'Main Item',
        'class_name' => 'MainItem',
        'classification' => 'Ship',
        'data' => [
            'stdItem' => [
                'Ports' => [
                    [
                        'PortName' => 'WeaponMount',
                        'EquippedItemUUID' => 'weapon-uuid',
                    ],
                ],
            ],
        ],
    ]);

    // Create equipped weapon
    $weapon = Item::create(['uuid' => 'weapon-uuid']);
    ItemData::create([
        'item_id' => $weapon->id,
        'game_version_id' => $version->id,
        'manufacturer_id' => $manufacturer->id,
        'name' => 'Version-Specific Weapon',
        'class_name' => 'Weapon',
        'classification' => 'WeaponGun',
        'data' => [],
    ]);

    $response = $this->getJson("/api/items/{$mainItem->uuid}?version=3.21.0-LIVE");

    $response->assertSuccessful()
        ->assertJsonPath('data.ports.0.equipped_item.name', 'Version-Specific Weapon')
        ->assertJsonPath('data.ports.0.equipped_item.version', '3.21.0-LIVE');
});
