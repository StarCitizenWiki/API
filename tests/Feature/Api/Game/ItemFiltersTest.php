<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\Game\Item;
use App\Models\Game\ItemData;
use App\Models\Game\Manufacturer;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns item filter values with counts', function (): void {
    $version = GameVersion::factory()->create([
        'code' => '3.25.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $manufacturer = Manufacturer::factory()->create([
        'name' => 'Acme',
        'code' => 'ACME',
    ]);

    $item = Item::factory()->create();
    ItemData::factory()
        ->for($item)
        ->for($version, 'gameVersion')
        ->for($manufacturer)
        ->create([
            'name' => 'Pulse Rifle',
            'type' => 'Weapon',
            'sub_type' => 'Laser',
            'classification' => 'FPS.Weapon',
            'size' => 1,
            'grade' => 2,
            'class' => 'A',
            'data' => [],
        ]);

    $unknownManufacturer = Manufacturer::factory()->create([
        'name' => 'Nova',
        'code' => 'NOVA',
    ]);

    $unknown = Item::factory()->create();
    ItemData::factory()
        ->for($unknown)
        ->for($version, 'gameVersion')
        ->for($unknownManufacturer)
        ->create([
            'name' => 'Mystery Item',
            'type' => null,
            'sub_type' => null,
            'classification' => null,
            'size' => null,
            'grade' => null,
            'class' => null,
            'data' => [],
        ]);

    $this->getJson(route('items.filters'))
        ->assertOk()
        ->assertExactJson([
            'filters' => [
                'type' => [
                    ['value' => 'Weapon', 'label' => 'Weapon', 'count' => 1],
                    ['value' => null, 'label' => 'Unknown', 'count' => 1],
                ],
                'sub_type' => [
                    ['value' => 'Laser', 'label' => 'Laser', 'count' => 1],
                    ['value' => null, 'label' => 'Unknown', 'count' => 1],
                ],
                'classification' => [
                    ['value' => 'FPS.Weapon', 'label' => 'FPS.Weapon', 'count' => 1],
                    ['value' => null, 'label' => 'Unknown', 'count' => 1],
                ],
                'size' => [
                    ['value' => 1, 'label' => '1', 'count' => 1],
                    ['value' => null, 'label' => 'Unknown', 'count' => 1],
                ],
                'grade' => [
                    ['value' => 2, 'label' => 'B', 'count' => 1],
                    ['value' => null, 'label' => 'Unknown', 'count' => 1],
                ],
                'class' => [
                    ['value' => 'A', 'label' => 'A', 'count' => 1],
                    ['value' => null, 'label' => 'Unknown', 'count' => 1],
                ],
                'manufacturer' => [
                    ['value' => 'Acme', 'label' => 'Acme', 'count' => 1],
                    ['value' => 'Nova', 'label' => 'Nova', 'count' => 1],
                ],
            ],
        ]);
});

it('filters item filter values by category', function (): void {
    $version = GameVersion::factory()->create([
        'code' => '3.25.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $manufacturer = Manufacturer::factory()->create([
        'name' => 'Category Co',
        'code' => 'CAT',
    ]);

    $foodItem = Item::factory()->create();
    ItemData::factory()
        ->for($foodItem)
        ->for($version, 'gameVersion')
        ->for($manufacturer)
        ->create([
            'name' => 'Energy Bar',
            'type' => 'Food',
            'sub_type' => 'Snack',
            'classification' => 'Test',
            'size' => 1,
            'grade' => 1,
            'class' => 'Civilian',
            'data' => [],
        ]);

    $weaponItem = Item::factory()->create();
    ItemData::factory()
        ->for($weaponItem)
        ->for($version, 'gameVersion')
        ->for($manufacturer)
        ->create([
            'name' => 'Laser Pistol',
            'type' => 'WeaponPersonal',
            'sub_type' => 'Pistol',
            'classification' => 'Test',
            'size' => 2,
            'grade' => 3,
            'class' => 'Military',
            'data' => [],
        ]);

    $this->getJson(route('items.filters', ['filter' => ['category' => 'food']]))
        ->assertOk()
        ->assertExactJson([
            'filters' => [
                'type' => [
                    ['value' => 'Food', 'label' => 'Food', 'count' => 1],
                ],
                'sub_type' => [
                    ['value' => 'Snack', 'label' => 'Snack', 'count' => 1],
                ],
                'classification' => [
                    ['value' => 'Test', 'label' => 'Test', 'count' => 1],
                ],
                'size' => [
                    ['value' => 1, 'label' => '1', 'count' => 1],
                ],
                'grade' => [
                    ['value' => 1, 'label' => 'A', 'count' => 1],
                ],
                'class' => [
                    ['value' => 'Civilian', 'label' => 'Civilian', 'count' => 1],
                ],
                'manufacturer' => [
                    ['value' => 'Category Co', 'label' => 'Category Co', 'count' => 1],
                ],
            ],
        ]);
});

it('filters item filter values by type', function (): void {
    $version = GameVersion::factory()->create([
        'code' => '3.25.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $manufacturer = Manufacturer::factory()->create([
        'name' => 'Type Co',
        'code' => 'TYPE',
    ]);

    $armorItem = Item::factory()->create();
    ItemData::factory()
        ->for($armorItem)
        ->for($version, 'gameVersion')
        ->for($manufacturer)
        ->create([
            'name' => 'Armor Core',
            'type' => 'Armor',
            'sub_type' => 'Light',
            'classification' => 'FPS.Armor',
            'size' => 3,
            'grade' => 4,
            'class' => 'Industrial',
            'data' => [],
        ]);

    $weaponItem = Item::factory()->create();
    ItemData::factory()
        ->for($weaponItem)
        ->for($version, 'gameVersion')
        ->for($manufacturer)
        ->create([
            'name' => 'Sidearm',
            'type' => 'Weapon',
            'sub_type' => 'Pistol',
            'classification' => 'FPS.Weapon',
            'size' => 1,
            'grade' => 2,
            'class' => 'Military',
            'data' => [],
        ]);

    $this->getJson(route('items.filters', ['filter' => ['type' => 'Armor']]))
        ->assertOk()
        ->assertExactJson([
            'filters' => [
                'type' => [
                    ['value' => 'Armor', 'label' => 'Armor', 'count' => 1],
                ],
                'sub_type' => [
                    ['value' => 'Light', 'label' => 'Light', 'count' => 1],
                ],
                'classification' => [
                    ['value' => 'FPS.Armor', 'label' => 'FPS.Armor', 'count' => 1],
                ],
                'size' => [
                    ['value' => 3, 'label' => '3', 'count' => 1],
                ],
                'grade' => [
                    ['value' => 4, 'label' => 'D', 'count' => 1],
                ],
                'class' => [
                    ['value' => 'Industrial', 'label' => 'Industrial', 'count' => 1],
                ],
                'manufacturer' => [
                    ['value' => 'Type Co', 'label' => 'Type Co', 'count' => 1],
                ],
            ],
        ]);
});
