<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\Game\Item;
use App\Models\Game\ItemData;
use App\Models\Game\Manufacturer;

beforeEach(function (): void {
    $this->defaultVersion = GameVersion::factory()->create([
        'code' => '4.0.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
    ]);

    $this->manufacturer = Manufacturer::factory()->create([
        'name' => 'Sorting Labs',
        'code' => 'SORT',
    ]);
});

it('sorts items by name ascending', function (): void {
    foreach ([
        ['name' => 'Gamma Core', 'class_name' => 'gamma_core'],
        ['name' => 'Alpha Core', 'class_name' => 'alpha_core'],
        ['name' => 'Delta Core', 'class_name' => 'delta_core'],
        ['name' => 'Beta Core', 'class_name' => 'beta_core'],
    ] as $item) {
        ItemData::factory()
            ->for($this->defaultVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                ...$item,
                'type' => 'Widget',
                'classification' => 'Test.Widget',
                'data' => ['stdItem' => []],
            ]);
    }

    $response = $this->getJson('/api/items?sort=name');

    $response->assertSuccessful();

    expect(collect($response->json('data'))->pluck('name')->toArray())
        ->toBe(['Alpha Core', 'Beta Core', 'Delta Core', 'Gamma Core']);
});

it('sorts items by grade descending', function (): void {
    foreach ([
        ['name' => 'Grade One', 'class_name' => 'grade_one', 'grade' => 1],
        ['name' => 'Grade Four', 'class_name' => 'grade_four', 'grade' => 4],
        ['name' => 'Grade Two', 'class_name' => 'grade_two', 'grade' => 2],
        ['name' => 'Grade Three', 'class_name' => 'grade_three', 'grade' => 3],
    ] as $item) {
        ItemData::factory()
            ->for($this->defaultVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                ...$item,
                'type' => 'Widget',
                'classification' => 'Test.Widget',
                'data' => ['stdItem' => []],
            ]);
    }

    $response = $this->getJson('/api/items?sort=-grade');

    $response->assertSuccessful();

    expect(collect($response->json('data'))->pluck('name')->toArray())
        ->toBe(['Grade Four', 'Grade Three', 'Grade Two', 'Grade One']);
});

it('sorts items by json numeric field weapon.damage.alphatotal descending', function (): void {
    foreach ([
        ['name' => 'Gun 400', 'class_name' => 'gun_400', 'alpha_total' => 400],
        ['name' => 'Gun 1200', 'class_name' => 'gun_1200', 'alpha_total' => 1200],
        ['name' => 'Gun 800', 'class_name' => 'gun_800', 'alpha_total' => 800],
        ['name' => 'Gun 50', 'class_name' => 'gun_50', 'alpha_total' => 50],
    ] as $weapon) {
        ItemData::factory()
            ->for($this->defaultVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => $weapon['name'],
                'class_name' => $weapon['class_name'],
                'type' => 'WeaponGun',
                'classification' => 'Ship.Weapon.Gun',
                'data' => [
                    'stdItem' => [
                        'Weapon' => [
                            'Damage' => [
                                'AlphaTotal' => $weapon['alpha_total'],
                            ],
                        ],
                    ],
                ],
            ]);
    }

    $response = $this->getJson('/api/items?filter[type]=WeaponGun&sort=-Weapon.Damage.AlphaTotal');

    $response->assertSuccessful();

    expect(collect($response->json('data'))->pluck('vehicle_weapon.damage.alpha_total')->toArray())
        ->toBe([1200, 800, 400, 50]);
});

it('sorts items by shieldcontroller.facetype text field ascending', function (): void {
    foreach ([
        ['name' => 'Shield Quad', 'class_name' => 'shield_quad', 'face_type' => 'Quad'],
        ['name' => 'Shield Single', 'class_name' => 'shield_single', 'face_type' => 'Single'],
        ['name' => 'Shield Dual', 'class_name' => 'shield_dual', 'face_type' => 'Dual'],
        ['name' => 'Shield AllAround', 'class_name' => 'shield_allaround', 'face_type' => 'AllAround'],
    ] as $shieldController) {
        ItemData::factory()
            ->for($this->defaultVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => $shieldController['name'],
                'class_name' => $shieldController['class_name'],
                'type' => 'ShieldController',
                'classification' => 'Ship.ShieldController',
                'data' => [
                    'stdItem' => [
                        'ShieldController' => [
                            'FaceType' => $shieldController['face_type'],
                        ],
                    ],
                ],
            ]);
    }

    $response = $this->getJson('/api/items?filter[type]=ShieldController&sort=ShieldController.FaceType');

    $response->assertSuccessful();

    expect(collect($response->json('data'))->pluck('shield_controller.face_type')->toArray())
        ->toBe(['AllAround', 'Dual', 'Quad', 'Single']);
});

it('supports exact multi-field sorting semantics for grade,-name', function (): void {
    foreach ([
        ['name' => 'Zulu', 'class_name' => 'grade_1_zulu', 'grade' => 1],
        ['name' => 'Alpha', 'class_name' => 'grade_1_alpha', 'grade' => 1],
        ['name' => 'Charlie', 'class_name' => 'grade_2_charlie', 'grade' => 2],
        ['name' => 'Bravo', 'class_name' => 'grade_2_bravo', 'grade' => 2],
        ['name' => 'Echo', 'class_name' => 'grade_3_echo', 'grade' => 3],
    ] as $item) {
        ItemData::factory()
            ->for($this->defaultVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                ...$item,
                'type' => 'Widget',
                'classification' => 'Test.Widget',
                'data' => ['stdItem' => []],
            ]);
    }

    $response = $this->getJson('/api/items?sort=grade,-name');

    $response->assertSuccessful();

    expect(collect($response->json('data'))->pluck('name')->toArray())
        ->toBe(['Zulu', 'Alpha', 'Charlie', 'Bravo', 'Echo']);
});

it('places null json values last when sorting ascending', function (): void {
    foreach ([
        ['name' => 'Weapon 300', 'class_name' => 'weapon_300', 'alpha_total' => 300],
        ['name' => 'Weapon 100', 'class_name' => 'weapon_100', 'alpha_total' => 100],
        ['name' => 'Weapon 200', 'class_name' => 'weapon_200', 'alpha_total' => 200],
    ] as $weapon) {
        ItemData::factory()
            ->for($this->defaultVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => $weapon['name'],
                'class_name' => $weapon['class_name'],
                'type' => 'WeaponGun',
                'classification' => 'Ship.Weapon.Gun',
                'data' => [
                    'stdItem' => [
                        'Weapon' => [
                            'Damage' => [
                                'AlphaTotal' => $weapon['alpha_total'],
                            ],
                        ],
                    ],
                ],
            ]);
    }

    foreach ([
        ['name' => 'Weapon Null A', 'class_name' => 'weapon_null_a'],
        ['name' => 'Weapon Null B', 'class_name' => 'weapon_null_b'],
    ] as $weaponWithoutValue) {
        ItemData::factory()
            ->for($this->defaultVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                ...$weaponWithoutValue,
                'type' => 'WeaponGun',
                'classification' => 'Ship.Weapon.Gun',
                'data' => ['stdItem' => []],
            ]);
    }

    $response = $this->getJson('/api/items?filter[type]=WeaponGun&sort=Weapon.Damage.AlphaTotal');

    $response->assertSuccessful();
    $data = collect($response->json('data'));

    expect($data->take(3)->pluck('name')->toArray())->toBe(['Weapon 100', 'Weapon 200', 'Weapon 300'])
        ->and($data->take(3)->pluck('vehicle_weapon.damage.alpha_total')->toArray())->toBe([100, 200, 300])
        ->and($data->slice(3)->every(
            fn (array $item): bool => data_get($item, 'vehicle_weapon.damage.alpha_total') === null
        ))->toBeTrue()
        ->and($data->slice(3)->pluck('name')->sort()->values()->toArray())->toBe(['Weapon Null A', 'Weapon Null B']);
});

it('places null json values last when sorting descending', function (): void {
    foreach ([
        ['name' => 'Shield 5000', 'class_name' => 'shield_5000', 'max_health' => 5000],
        ['name' => 'Shield 7000', 'class_name' => 'shield_7000', 'max_health' => 7000],
        ['name' => 'Shield 6000', 'class_name' => 'shield_6000', 'max_health' => 6000],
    ] as $shield) {
        ItemData::factory()
            ->for($this->defaultVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => $shield['name'],
                'class_name' => $shield['class_name'],
                'type' => 'Shield',
                'classification' => 'Ship.Shield',
                'data' => [
                    'stdItem' => [
                        'Shield' => [
                            'MaxShieldHealth' => $shield['max_health'],
                        ],
                    ],
                ],
            ]);
    }

    foreach ([
        ['name' => 'Shield Null A', 'class_name' => 'shield_null_a'],
        ['name' => 'Shield Null B', 'class_name' => 'shield_null_b'],
    ] as $shieldWithoutValue) {
        ItemData::factory()
            ->for($this->defaultVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                ...$shieldWithoutValue,
                'type' => 'Shield',
                'classification' => 'Ship.Shield',
                'data' => ['stdItem' => []],
            ]);
    }

    $response = $this->getJson('/api/items?filter[type]=Shield&sort=-Shield.MaxShieldHealth');

    $response->assertSuccessful();
    $data = collect($response->json('data'));

    expect($data->take(3)->pluck('name')->toArray())->toBe(['Shield 7000', 'Shield 6000', 'Shield 5000'])
        ->and($data->take(3)->pluck('shield.max_health')->toArray())->toBe([7000, 6000, 5000])
        ->and($data->slice(3)->every(
            fn (array $item): bool => data_get($item, 'shield.max_health') === null
        ))->toBeTrue()
        ->and($data->slice(3)->pluck('name')->sort()->values()->toArray())->toBe(['Shield Null A', 'Shield Null B']);
});

it('returns pagination metadata and a correctly sorted subset for -grade', function (): void {
    foreach ([
        ['name' => 'Grade Three', 'class_name' => 'grade_3', 'grade' => 3],
        ['name' => 'Grade Seven', 'class_name' => 'grade_7', 'grade' => 7],
        ['name' => 'Grade One', 'class_name' => 'grade_1', 'grade' => 1],
        ['name' => 'Grade Six', 'class_name' => 'grade_6', 'grade' => 6],
        ['name' => 'Grade Two', 'class_name' => 'grade_2', 'grade' => 2],
        ['name' => 'Grade Five', 'class_name' => 'grade_5', 'grade' => 5],
        ['name' => 'Grade Four', 'class_name' => 'grade_4', 'grade' => 4],
    ] as $item) {
        ItemData::factory()
            ->for($this->defaultVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                ...$item,
                'type' => 'Widget',
                'classification' => 'Test.Widget',
                'data' => ['stdItem' => []],
            ]);
    }

    $response = $this->getJson('/api/items?sort=-grade&page[size]=3&page[number]=2');

    $response->assertSuccessful();

    expect($response->json('meta.per_page'))->toBe(3)
        ->and($response->json('meta.current_page'))->toBe(2)
        ->and($response->json('meta.total'))->toBe(7)
        ->and($response->json('meta.last_page'))->toBe(3)
        ->and(collect($response->json('data'))->pluck('name')->toArray())->toBe([
            'Grade Four',
            'Grade Three',
            'Grade Two',
        ]);
});

it('sorts items by json mass ascending and places null values last', function (): void {
    ItemData::factory()
        ->for($this->defaultVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'name' => 'Zulu Item',
            'type' => 'Widget',
            'class_name' => 'zulu_item',
            'classification' => 'Test.Widget',
            'mass' => 50.0,
            'data' => ['stdItem' => ['Mass' => 50.0]],
        ]);

    ItemData::factory()
        ->for($this->defaultVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'name' => 'Alpha Item',
            'type' => 'Widget',
            'class_name' => 'alpha_item',
            'classification' => 'Test.Widget',
            'mass' => 150.0,
            'data' => ['stdItem' => ['Mass' => 150.0]],
        ]);

    ItemData::factory()
        ->for($this->defaultVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'name' => 'Hotel Item',
            'type' => 'Widget',
            'class_name' => 'hotel_item',
            'classification' => 'Test.Widget',
            'data' => ['stdItem' => []],
        ]);

    $response = $this->getJson('/api/items?sort=Mass');

    $response->assertSuccessful()
        ->assertJsonCount(3, 'data');

    expect(collect($response->json('data'))->pluck('name')->all())
        ->toBe(['Zulu Item', 'Alpha Item', 'Hotel Item']);
})->group('db-pgsql');

it('sorts items by json mass descending and places null values last', function (): void {
    ItemData::factory()
        ->for($this->defaultVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'name' => 'Zulu Item',
            'type' => 'Widget',
            'class_name' => 'zulu_item',
            'classification' => 'Test.Widget',
            'mass' => 50.0,
            'data' => ['stdItem' => ['Mass' => 50.0]],
        ]);

    ItemData::factory()
        ->for($this->defaultVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'name' => 'Alpha Item',
            'type' => 'Widget',
            'class_name' => 'alpha_item',
            'classification' => 'Test.Widget',
            'mass' => 150.0,
            'data' => ['stdItem' => ['Mass' => 150.0]],
        ]);

    ItemData::factory()
        ->for($this->defaultVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'name' => 'Hotel Item',
            'type' => 'Widget',
            'class_name' => 'hotel_item',
            'classification' => 'Test.Widget',
            'data' => ['stdItem' => []],
        ]);

    $response = $this->getJson('/api/items?sort=-Mass');

    $response->assertSuccessful()
        ->assertJsonCount(3, 'data');

    expect(collect($response->json('data'))->pluck('name')->all())
        ->toBe(['Alpha Item', 'Zulu Item', 'Hotel Item']);
})->group('db-pgsql');

it('sorts items by manufacturer name', function (): void {
    $alphaManufacturer = Manufacturer::factory()->create([
        'name' => 'Alpha Corp',
        'code' => 'ALPHA',
    ]);

    $betaManufacturer = Manufacturer::factory()->create([
        'name' => 'Beta Corp',
        'code' => 'BETA',
    ]);

    $alphaItem = Item::factory()->create();
    ItemData::factory()
        ->for($alphaItem)
        ->for($this->defaultVersion, 'gameVersion')
        ->for($alphaManufacturer)
        ->create([
            'name' => 'Alpha Item',
            'type' => 'Widget',
            'class_name' => 'alpha_item',
            'classification' => 'Test',
            'data' => [],
        ]);

    $betaItem = Item::factory()->create();
    ItemData::factory()
        ->for($betaItem)
        ->for($this->defaultVersion, 'gameVersion')
        ->for($betaManufacturer)
        ->create([
            'name' => 'Beta Item',
            'type' => 'Widget',
            'class_name' => 'beta_item',
            'classification' => 'Test',
            'data' => [],
        ]);

    $response = $this->getJson('/api/items?sort=manufacturer.name');

    $response->assertSuccessful()
        ->assertJsonPath('data.0.uuid', $alphaItem->uuid)
        ->assertJsonPath('data.1.uuid', $betaItem->uuid);
});
