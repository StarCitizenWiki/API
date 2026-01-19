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
                'stdItem' => [
                    'Weapon' => ['Damage' => ['AlphaTotal' => $damage]],
                ],
            ],
        ]);
    }

    $response = $this->getJson('/api/items?filter[type]=WeaponGun&sort=-Weapon.Damage.AlphaTotal');

    $response->assertSuccessful();
    $returned = collect($response->json('data'))->pluck('personal_weapon.damage.alpha_total')->toArray();
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

    $response = $this->getJson('/api/items?filter[type]=ShieldController&sort=ShieldController.FaceTyp');

    $response->assertSuccessful();
    $returned = collect($response->json('data'))->pluck('shield_controller.face_type')->toArray();
    expect($returned)->toBe(['Dual', 'Quad', 'Single']); // Alphabetical
});

it('places null values last when sorting ascending', function () {
    if (! $this->isPostgreSQL) {
        $this->markTestSkipped('JSON sorting requires PostgreSQL');
    }

    ItemData::factory()->count(3)->create([
        'game_version_id' => $this->defaultVersion->id,
        'type' => 'WeaponGun',
        'data' => [
            'stdItem' => [
                'Weapon' => ['Damage' => ['AlphaTotal' => random_int(100, 1000)]],
            ],
        ],
    ]);

    ItemData::factory()->count(2)->create([
        'game_version_id' => $this->defaultVersion->id,
        'type' => 'WeaponGun',
        'data' => [],  // No weapon damage data
    ]);

    $response = $this->getJson('/api/items?filter[type]=WeaponGun&sort=Weapon.Damage.AlphaTotal');

    $response->assertSuccessful();
    $data = collect($response->json('data'));

    // First 3 should have values, last 2 should be null
    expect($data->take(3)->every(fn ($item) => isset($item['personal_weapon']['damage']['alpha_total'])))->toBeTrue()
        ->and($data->slice(3)->every(fn ($item) => ! isset($item['personal_weapon']['damage']['alpha_total'])))->toBeTrue();
});

it('places null values last when sorting descending', function () {
    if (! $this->isPostgreSQL) {
        $this->markTestSkipped('JSON sorting requires PostgreSQL');
    }

    ItemData::factory()->count(3)->create([
        'game_version_id' => $this->defaultVersion->id,
        'type' => 'Shield',
        'data' => [
            'stdItem' => [
                ['Shield' => ['MaxShieldHealth' => random_int(5000, 20000)]],
            ],
        ],
    ]);

    ItemData::factory()->count(2)->create([
        'game_version_id' => $this->defaultVersion->id,
        'type' => 'Shield',
        'data' => [],
    ]);

    $response = $this->getJson('/api/items?filter[type]=Shield&sort=-Shield.MaxShieldHealth');

    $response->assertSuccessful();
    $data = collect($response->json('data'));

    // First 3 should have values (descending), last 2 should be null
    expect($data->take(3)->every(fn ($item) => isset($item['data']['shield']['max_health'])))->toBeTrue()
        ->and($data->slice(3)->every(fn ($item) => ! isset($item['data']['shield']['max_health'])))->toBeTrue();
});

it('supports multiple field sorting', function () {
    if (! $this->isPostgreSQL) {
        $this->markTestSkipped('JSON sorting requires PostgreSQL');
    }

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
        'data' => [
            'stdItem' => ['Weapon' => ['RateOfFire' => random_int(100, 500)]],
        ],
    ]);

    ItemData::factory()->count(2)->create([
        'game_version_id' => $this->defaultVersion->id,
        'type' => 'Shield',
        'data' => ['stdItem' => ['Shield' => ['MaxShieldHealth' => random_int(5000, 10000)]]],
    ]);

    $response = $this->getJson('/api/items?filter[type]=WeaponGun&sort=-Weapon.RateOfFire');

    $response->assertSuccessful();
    expect($response->json('meta.total'))->toBe(3);

    $rateOfFires = collect($response->json('data'))->pluck('personal_weapon.rpm')->toArray();
    expect($rateOfFires)->toBe(collect($rateOfFires)->sortDesc()->values()->toArray());
});

it('works with pagination', function () {
    ItemData::factory()->count(15)->create([
        'game_version_id' => $this->defaultVersion->id,
        'grade' => fn () => random_int(1, 7),
    ]);

    $response = $this->getJson('/api/items?sort=-grade&page[size]=5&page[number]=1');

    $response->assertSuccessful();
    expect($response->json('meta.per_page'))->toBe(5)
        ->and($response->json('meta.current_page'))->toBe(1);

    $grades = collect($response->json('data'))->pluck('grade')->toArray();
    expect($grades)->toBe(collect($grades)->sortDesc()->values()->toArray());
});

it('sorts by mining laser power transfer', function () {
    if (! $this->isPostgreSQL) {
        $this->markTestSkipped('JSON sorting requires PostgreSQL');
    }

    $powers = [100, 500, 300, 200];

    foreach ($powers as $power) {
        ItemData::factory()->create([
            'game_version_id' => $this->defaultVersion->id,
            'type' => 'WeaponMining',
            'data' => [
                'stdItem' => [
                    'MiningLaser' => ['PowerTransfer' => $power],
                ],
            ],
        ]);
    }

    $response = $this->getJson('/api/items?filter[type]=WeaponMining&sort=-MiningLaser.PowerTransfer');

    $response->assertSuccessful();
    $returned = collect($response->json('data'))->pluck('mining_laser.laser_power.maximum')->toArray();
    expect($returned)->toBe([500, 300, 200, 100]);
});

it('sorts by missile damage total', function () {
    if (! $this->isPostgreSQL) {
        $this->markTestSkipped('JSON sorting requires PostgreSQL');
    }

    $damages = [1000, 500, 750];

    foreach ($damages as $damage) {
        ItemData::factory()->create([
            'game_version_id' => $this->defaultVersion->id,
            'type' => 'Missile',
            'data' => [
                'stdItem' => [
                    'Missile' => ['DamageTotal' => $damage],
                ],
            ],
        ]);
    }

    $response = $this->getJson('/api/items?filter[type]=Missile&sort=Missile.DamageTotal');

    $response->assertSuccessful();
    $returned = collect($response->json('data'))->pluck('data.missile.damage_total')->toArray();
    expect($returned)->toBe([500, 750, 1000]);
});
