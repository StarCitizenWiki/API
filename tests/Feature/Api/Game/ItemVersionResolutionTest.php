<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\Game\Item;
use App\Models\Game\ItemData;
use App\Models\Game\Manufacturer;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('resolves default game version when no version parameter provided', function (): void {
    // Create a default game version
    $defaultVersion = GameVersion::factory()->create([
        'code' => '3.21.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    // Create a manufacturer
    $manufacturer = Manufacturer::factory()->create([
        'name' => 'Test Manufacturer',
        'code' => 'TEST',
    ]);

    // Create an item with data for the default version
    $item = Item::factory()->create();
    ItemData::factory()
        ->for($item)
        ->for($defaultVersion, 'gameVersion')
        ->for($manufacturer)
        ->create([
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

it('resolves specific game version from version parameter', function (): void {
    // Create multiple versions
    $oldVersion = GameVersion::factory()->create([
        'code' => '3.20.0-LIVE',
        'channel' => 'live',
        'is_default' => false,
        'released_at' => now()->subDays(7),
    ]);

    $newVersion = GameVersion::factory()->create([
        'code' => '3.21.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    // Create a manufacturer
    $manufacturer = Manufacturer::factory()->create([
        'name' => 'Test Manufacturer',
        'code' => 'TEST',
    ]);

    // Create an item with data for both versions
    $item = Item::factory()->create();

    ItemData::factory()
        ->for($item)
        ->for($oldVersion, 'gameVersion')
        ->for($manufacturer)
        ->create([
            'name' => 'Old Version Item',
            'class_name' => 'TestItem',
            'classification' => 'TestClass',
            'data' => [],
        ]);

    ItemData::factory()
        ->for($item)
        ->for($newVersion, 'gameVersion')
        ->for($manufacturer)
        ->create([
            'name' => 'New Version Item',
            'class_name' => 'TestItem',
            'classification' => 'TestClass',
            'data' => [],
        ]);

    // Request the old version specifically
    $response = $this->getJson("/api/items/{$item->uuid}?version=3.20.0-LIVE");

    $this->assertEquals('3.20.0-LIVE', $response->json('data.version'));

    $response->assertSuccessful()
        ->assertJsonPath('data.name', 'Old Version Item');

    // Request the new version specifically
    $response = $this->getJson("/api/items/{$item->uuid}?version=3.21.0-LIVE");

    $response->assertSuccessful()
        ->assertJsonPath('data.name', 'New Version Item');
});

it('loads equipped items with correct game version', function (): void {
    $version = GameVersion::factory()->create([
        'code' => '3.21.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    // Create a manufacturer
    $manufacturer = Manufacturer::factory()->create([
        'name' => 'Test Manufacturer',
        'code' => 'TEST',
    ]);

    // Create main item with port data
    $mainItem = Item::factory()->create();
    ItemData::factory()
        ->for($mainItem)
        ->for($version, 'gameVersion')
        ->for($manufacturer)
        ->create([
            'name' => 'Main Item',
            'class_name' => 'MainItem',
            'classification' => 'Ship',
            'data' => [
                'stdItem' => [
                    'Ports' => [
                        [
                            'PortName' => 'WeaponMount',
                            'EquippedItem' => 'weapon-uuid',
                        ],
                    ],
                ],
            ],
        ]);

    // Create equipped weapon
    $weapon = Item::factory()->create([
        'uuid' => 'weapon-uuid',
    ]);
    ItemData::factory()
        ->for($weapon)
        ->for($version, 'gameVersion')
        ->for($manufacturer)
        ->create([
            'name' => 'Version-Specific Weapon',
            'class_name' => 'Weapon',
            'classification' => 'WeaponGun',
            'data' => [],
        ]);

    $response = $this->getJson("/api/items/{$mainItem->uuid}?version=3.21.0-LIVE");

    $response->assertSuccessful()
        // ->assertJsonPath('data.ports.0.equipped_item.name', 'Version-Specific Weapon')
        ->assertJsonPath('data.ports.0.equipped_item.uuid', 'weapon-uuid')
        ->assertJsonPath('data.ports.0.equipped_item.version', '3.21.0-LIVE');
});
