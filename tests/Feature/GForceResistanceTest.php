<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\Game\Item;
use App\Models\Game\ItemData;
use App\Models\Game\Manufacturer;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns gforce_resistance at root level for clothing with GForceResistance data', function (): void {
    $version = GameVersion::factory()->create([
        'code' => '4.4.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $manufacturer = Manufacturer::factory()->create([
        'name' => 'Test Manufacturer',
        'code' => 'TEST',
    ]);

    $item = Item::factory()->create();

    ItemData::factory()
        ->for($item)
        ->for($version, 'gameVersion')
        ->for($manufacturer)
        ->create([
            'name' => 'Test Jacket',
            'type' => 'Char_Clothing_Torso_1',
            'class_name' => 'test_jacket_01',
            'classification' => 'FPS.Clothing.Torso_1',
            'data' => [
                'stdItem' => [
                    'GForceResistance' => ['Value' => -0.125],
                    'TemperatureResistance' => ['Minimum' => -10, 'Maximum' => 40],
                ],
            ],
        ]);

    $response = $this->getJson("/api/items/{$item->uuid}");

    $response->assertSuccessful()
        ->assertJsonPath('data.gforce_resistance', -0.125)
        ->assertJsonPath('data.clothing.gforce_resistance', -0.125);
});

it('returns gforce_resistance at root level for armor with positive value', function (): void {
    $version = GameVersion::factory()->create([
        'code' => '4.4.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $manufacturer = Manufacturer::factory()->create([
        'name' => 'Test Manufacturer',
        'code' => 'TEST',
    ]);

    $item = Item::factory()->create();

    ItemData::factory()
        ->for($item)
        ->for($version, 'gameVersion')
        ->for($manufacturer)
        ->create([
            'name' => 'Test Undersuit',
            'type' => 'Char_Armor_Undersuit',
            'class_name' => 'test_undersuit_01',
            'classification' => 'FPS.Armor.Undersuit',
            'data' => [
                'stdItem' => [
                    'GForceResistance' => ['Value' => 0.9],
                    'SuitArmor' => [
                        'DamageResistance' => [
                            'Impact' => 0.5,
                        ],
                    ],
                ],
            ],
        ]);

    $response = $this->getJson("/api/items/{$item->uuid}");

    $response->assertSuccessful()
        ->assertJsonPath('data.gforce_resistance', 0.9)
        ->assertJsonPath('data.suit_armor.gforce_resistance', 0.9);
});

it('omits gforce_resistance when item has no GForceResistance data', function (): void {
    $version = GameVersion::factory()->create([
        'code' => '4.4.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $manufacturer = Manufacturer::factory()->create([
        'name' => 'Test Manufacturer',
        'code' => 'TEST',
    ]);

    $item = Item::factory()->create();

    ItemData::factory()
        ->for($item)
        ->for($version, 'gameVersion')
        ->for($manufacturer)
        ->create([
            'name' => 'Test Shield',
            'type' => 'Shield',
            'class_name' => 'test_shield_01',
            'classification' => 'Ship.Shield',
            'data' => [
                'stdItem' => [
                    'Shield' => [
                        'MaxShieldHealth' => 1000,
                    ],
                ],
            ],
        ]);

    $response = $this->getJson("/api/items/{$item->uuid}");

    $response->assertSuccessful()
        ->assertJsonMissingPath('data.gforce_resistance');
});
