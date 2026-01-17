<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\Game\ItemData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->defaultVersion = GameVersion::factory()->create(['is_default' => true]);
    $this->isPostgreSQL = DB::connection()->getDriverName() === 'pgsql';
});

it('sorts items by name ascending', function () {
    ItemData::factory()->count(5)->create([
        'game_version_id' => $this->defaultVersion->id,
        'name' => fake()->unique()->word(),
    ]);

    $response = $this->getJson('/api/items?sort=name');

    $response->assertSuccessful();
    $names = collect($response->json('data'))->pluck('name')->toArray();
    expect($names)->toBe(collect($names)->sort()->values()->toArray());
});

it('sorts items by grade descending', function () {
    ItemData::factory()->count(5)->create([
        'game_version_id' => $this->defaultVersion->id,
        'grade' => fake()->numberBetween(1, 7),
    ]);

    $response = $this->getJson('/api/items?sort=-grade');

    $response->assertSuccessful();
    $grades = collect($response->json('data'))->pluck('grade')->toArray();
    expect($grades)->toBe(collect($grades)->sortDesc()->values()->toArray());
});

it('sorts items by JSON numeric field weapon damage alpha total', function () {
    if (! $this->isPostgreSQL) {
        $this->markTestSkipped('JSON sorting requires PostgreSQL');
    }

    $damages = [500, 1000, 250, 750, 100];

    foreach ($damages as $damage) {
        ItemData::factory()->create([
            'game_version_id' => $this->defaultVersion->id,
            'type' => 'WeaponGun',
            'data' => [
                'Weapon' => ['Damage' => ['AlphaTotal' => $damage]],
            ],
        ]);
    }

    $response = $this->getJson('/api/items?filter[type]=WeaponGun&sort=-weapon.damage.alpha_total');

    $response->assertSuccessful();
    $returned = collect($response->json('data'))->pluck('data.Weapon.Damage.AlphaTotal')->toArray();
    expect($returned)->toBe([1000, 750, 500, 250, 100]);
});

it('sorts items by text JSON field shield controller face type', function () {
    if (! $this->isPostgreSQL) {
        $this->markTestSkipped('JSON sorting requires PostgreSQL');
    }

    $faceTypes = ['Quad', 'Single', 'Dual'];

    foreach ($faceTypes as $faceType) {
        ItemData::factory()->create([
            'game_version_id' => $this->defaultVersion->id,
            'type' => 'ShieldController',
            'data' => [
                'ShieldController' => ['FaceType' => $faceType],
            ],
        ]);
    }

    $response = $this->getJson('/api/items?filter[type]=ShieldController&sort=shield_controller.face_type');

    $response->assertSuccessful();
    $returned = collect($response->json('data'))->pluck('data.ShieldController.FaceType')->toArray();
    expect($returned)->toBe(['Dual', 'Quad', 'Single']); // Alphabetical
});

it('places null values last when sorting ascending', function () {
    if (! $this->isPostgreSQL) {
        $this->markTestSkipped('JSON sorting requires PostgreSQL');
    }

    ItemData::factory()->count(3)->create([
        'game_version_id' => $this->defaultVersion->id,
        'type' => 'WeaponGun',
        'data' => ['Weapon' => ['Damage' => ['AlphaTotal' => rand(100, 1000)]]],
    ]);

    ItemData::factory()->count(2)->create([
        'game_version_id' => $this->defaultVersion->id,
        'type' => 'WeaponGun',
        'data' => [],  // No weapon damage data
    ]);

    $response = $this->getJson('/api/items?filter[type]=WeaponGun&sort=weapon.damage.alpha_total');

    $response->assertSuccessful();
    $data = collect($response->json('data'));

    // First 3 should have values, last 2 should be null
    expect($data->take(3)->every(fn ($item) => isset($item['data']['Weapon']['Damage']['AlphaTotal'])))->toBeTrue();
    expect($data->slice(3)->every(fn ($item) => ! isset($item['data']['Weapon']['Damage']['AlphaTotal'])))->toBeTrue();
});

it('places null values last when sorting descending', function () {
    ItemData::factory()->count(3)->create([
        'game_version_id' => $this->defaultVersion->id,
        'type' => 'Shield',
        'data' => ['Shield' => ['MaxShieldHealth' => rand(5000, 20000)]],
    ]);

    ItemData::factory()->count(2)->create([
        'game_version_id' => $this->defaultVersion->id,
        'type' => 'Shield',
        'data' => [],
    ]);

    $response = $this->getJson('/api/items?filter[type]=Shield&sort=-shield.max_health');

    $response->assertSuccessful();
    $data = collect($response->json('data'));

    // First 3 should have values (descending), last 2 should be null
    expect($data->take(3)->every(fn ($item) => isset($item['data']['Shield']['MaxShieldHealth'])))->toBeTrue();
    expect($data->slice(3)->every(fn ($item) => ! isset($item['data']['Shield']['MaxShieldHealth'])))->toBeTrue();
});

it('supports multiple field sorting', function () {
    ItemData::factory()->create(['game_version_id' => $this->defaultVersion->id, 'grade' => 1, 'name' => 'Zulu']);
    ItemData::factory()->create(['game_version_id' => $this->defaultVersion->id, 'grade' => 1, 'name' => 'Alpha']);
    ItemData::factory()->create(['game_version_id' => $this->defaultVersion->id, 'grade' => 2, 'name' => 'Charlie']);
    ItemData::factory()->create(['game_version_id' => $this->defaultVersion->id, 'grade' => 2, 'name' => 'Bravo']);

    $response = $this->getJson('/api/items?sort=grade,-name');

    $response->assertSuccessful();
    $items = collect($response->json('data'));

    // Should be: grade 1 (Zulu, Alpha desc), grade 2 (Charlie, Bravo desc)
    expect($items->pluck('name')->toArray())->toBe(['Zulu', 'Alpha', 'Charlie', 'Bravo']);
});

it('combines JSON sorting with filtering', function () {
    ItemData::factory()->count(3)->create([
        'game_version_id' => $this->defaultVersion->id,
        'type' => 'WeaponGun',
        'data' => ['Weapon' => ['RateOfFire' => rand(100, 500)]],
    ]);

    ItemData::factory()->count(2)->create([
        'game_version_id' => $this->defaultVersion->id,
        'type' => 'Shield',
        'data' => ['Shield' => ['MaxShieldHealth' => rand(5000, 10000)]],
    ]);

    $response = $this->getJson('/api/items?filter[type]=WeaponGun&sort=-weapon.rate_of_fire');

    $response->assertSuccessful();
    expect($response->json('meta.total'))->toBe(3);

    $rateOfFires = collect($response->json('data'))->pluck('data.Weapon.RateOfFire')->toArray();
    expect($rateOfFires)->toBe(collect($rateOfFires)->sortDesc()->values()->toArray());
});

it('works with pagination', function () {
    ItemData::factory()->count(15)->create([
        'game_version_id' => $this->defaultVersion->id,
        'grade' => fn () => rand(1, 7),
    ]);

    $response = $this->getJson('/api/items?sort=-grade&page[size]=5&page[number]=1');

    $response->assertSuccessful();
    expect($response->json('meta.per_page'))->toBe(5);
    expect($response->json('meta.current_page'))->toBe(1);

    $grades = collect($response->json('data'))->pluck('grade')->toArray();
    expect($grades)->toBe(collect($grades)->sortDesc()->values()->toArray());
});

it('sorts deeply nested JSON paths correctly', function () {
    $modifiers = [0.5, 1.5, 0.8, 1.2];

    foreach ($modifiers as $modifier) {
        ItemData::factory()->create([
            'game_version_id' => $this->defaultVersion->id,
            'type' => 'WeaponModifier',
            'data' => [
                'WeaponModifier' => [
                    'WeaponStats' => [
                        'Base' => [
                            'DamageMultiplier' => $modifier,
                        ],
                    ],
                ],
            ],
        ]);
    }

    $response = $this->getJson('/api/items?filter[type]=WeaponModifier&sort=weapon_modifier.base.damage_change');

    $response->assertSuccessful();
    $returned = collect($response->json('data'))
        ->pluck('data.WeaponModifier.WeaponStats.Base.DamageMultiplier')
        ->toArray();
    expect($returned)->toBe([0.5, 0.8, 1.2, 1.5]);
});

it('sorts by mining laser power transfer', function () {
    $powers = [100, 500, 300, 200];

    foreach ($powers as $power) {
        ItemData::factory()->create([
            'game_version_id' => $this->defaultVersion->id,
            'type' => 'WeaponMining',
            'data' => [
                'MiningLaser' => ['PowerTransfer' => $power],
            ],
        ]);
    }

    $response = $this->getJson('/api/items?filter[type]=WeaponMining&sort=-mining_laser.power_transfer');

    $response->assertSuccessful();
    $returned = collect($response->json('data'))->pluck('data.MiningLaser.PowerTransfer')->toArray();
    expect($returned)->toBe([500, 300, 200, 100]);
});

it('sorts by missile damage total', function () {
    $damages = [1000, 500, 750];

    foreach ($damages as $damage) {
        ItemData::factory()->create([
            'game_version_id' => $this->defaultVersion->id,
            'type' => 'Missile',
            'data' => [
                'Missile' => ['DamageTotal' => $damage],
            ],
        ]);
    }

    $response = $this->getJson('/api/items?filter[type]=Missile&sort=missile.damage_total');

    $response->assertSuccessful();
    $returned = collect($response->json('data'))->pluck('data.Missile.DamageTotal')->toArray();
    expect($returned)->toBe([500, 750, 1000]);
});
