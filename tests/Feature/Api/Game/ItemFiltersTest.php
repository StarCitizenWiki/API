<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\Game\Item;
use App\Models\Game\ItemData;
use App\Models\Game\Manufacturer;

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
            'type' => 'Unknown',
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
                    ['value' => 'Unknown', 'label' => 'Unknown', 'count' => 1],
                    ['value' => 'Weapon', 'label' => 'Weapon', 'count' => 1],
                ],
                'sub_type' => [
                    ['value' => 'Laser', 'label' => 'Laser', 'count' => 1],
                    ['value' => null, 'label' => 'Unknown', 'count' => 1],
                ],
                'classification' => [
                    ['value' => 'FPS.Weapon', 'label' => 'Weapon', 'count' => 1],
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
                'event_source' => [],
                'manufacturer' => [
                    ['value' => 'Acme', 'label' => 'Acme', 'count' => 1],
                    ['value' => 'Nova', 'label' => 'Nova', 'count' => 1],
                ],
                'rarity' => [
                    ['value' => null, 'label' => 'Unknown', 'count' => 2],
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
                'event_source' => [],
                'manufacturer' => [
                    ['value' => 'Category Co', 'label' => 'Category Co', 'count' => 1],
                ],
                'rarity' => [
                    ['value' => null, 'label' => 'Unknown', 'count' => 1],
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
                    ['value' => 'FPS.Armor', 'label' => 'Armor', 'count' => 1],
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
                'event_source' => [],
                'manufacturer' => [
                    ['value' => 'Type Co', 'label' => 'Type Co', 'count' => 1],
                ],
                'rarity' => [
                    ['value' => null, 'label' => 'Unknown', 'count' => 1],
                ],
            ],
        ]);
});

it('resolves classification labels correctly', function (): void {
    $version = GameVersion::factory()->create([
        'code' => '3.25.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $manufacturer = Manufacturer::factory()->create([
        'name' => 'Test Co',
        'code' => 'TEST',
    ]);

    $classifications = [
        ['classification' => 'FPS.Clothing.Torso', 'expected_label' => 'Jacket'],
        ['classification' => 'FPS.Clothing.Legs', 'expected_label' => 'Pants'],
        ['classification' => 'FPS.Clothing.Hat', 'expected_label' => 'Hat'],
        ['classification' => 'FPS.Armor.Helmet', 'expected_label' => 'Helmet'],
        ['classification' => 'FPS.Weapon.Large', 'expected_label' => 'Large Weapon'],
        ['classification' => 'FPS.Weapon.Small', 'expected_label' => 'Small Weapon'],
        ['classification' => 'Mining.Gadget', 'expected_label' => 'Mining Gadget'],
        ['classification' => 'Mining.Module', 'expected_label' => 'Mining Module'],
        ['classification' => 'Ship.Turret.BallTurret', 'expected_label' => 'Ball Turret'],
        ['classification' => 'Ship.Weapon.Gun', 'expected_label' => 'Gun'],
    ];

    foreach ($classifications as $c) {
        ItemData::factory()
            ->for(Item::factory(), 'item')
            ->for($version, 'gameVersion')
            ->for($manufacturer)
            ->create([
                'name' => fake()->word(),
                'type' => 'Test',
                'classification' => $c['classification'],
                'size' => 1,
                'grade' => 1,
                'class' => 'A',
                'data' => [],
            ]);
    }

    $response = $this->getJson(route('items.filters'))
        ->assertOk();

    $classificationFilters = collect($response->json('filters.classification'));

    foreach ($classifications as $c) {
        $entry = $classificationFilters->first(fn (array $f) => $f['value'] === $c['classification']);
        expect($entry)->not->toBeNull("Classification {$c['classification']} not found in filters")
            ->and($entry['label'])->toBe($c['expected_label'], "Label for {$c['classification']}");
    }
});

it('includes rarity facet in filters endpoint', function (): void {
    $version = GameVersion::factory()->create([
        'code' => '3.25.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $manufacturer = Manufacturer::factory()->create();

    foreach (['Common', 'Rare', 'Rare'] as $rarity) {
        ItemData::factory()
            ->for(Item::factory(), 'item')
            ->for($version, 'gameVersion')
            ->for($manufacturer)
            ->create([
                'name' => fake()->word(),
                'type' => 'Weapon',
                'classification' => 'Test',
                'data' => [
                    'stdItem' => [
                        'Rarity' => $rarity,
                    ],
                ],
                'rarity' => $rarity,
            ]);
    }

    $response = $this->getJson(route('items.filters'));
    $response->assertOk();

    $rarityFilters = collect($response->json('filters.rarity'));

    $common = $rarityFilters->first(fn (array $f) => $f['value'] === 'Common');
    $rare = $rarityFilters->first(fn (array $f) => $f['value'] === 'Rare');

    expect($common)->not->toBeNull()
        ->and($common['count'])->toBe(1)
        ->and($rare)->not->toBeNull()
        ->and($rare['count'])->toBe(2);
});
