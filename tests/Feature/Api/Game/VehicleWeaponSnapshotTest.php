<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\Game\Manufacturer;
use App\Models\Game\Vehicle;
use App\Models\Game\VehicleData;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->manufacturer = Manufacturer::factory()->create([
        'name' => 'Test Manufacturer',
        'code' => 'TEST',
    ]);

    $this->gameVersion = GameVersion::factory()->create([
        'code' => '4.4.0-TEST',
        'channel' => 'test',
        'is_default' => true,
    ]);
});

it('includes weapon snapshot on v3 show route', function (): void {
    $vehicle = Vehicle::factory()->create();

    VehicleData::factory()
        ->for($vehicle)
        ->for($this->gameVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'name' => 'Test Fighter',
            'class_name' => 'TEST_Fighter',
            'data' => [
                'Loadout' => [
                    [
                        'Type' => 'WeaponGun.Gun',
                        'HardpointName' => 'hardpoint_weapon_nose',
                        'ClassName' => 'KLWE_LaserRepeater_S4',
                    ],
                    [
                        'Type' => 'WeaponGun.Gun',
                        'HardpointName' => 'hardpoint_weapon_left',
                        'ClassName' => 'KSAR_BallisticGatling_S2',
                    ],
                    [
                        'Type' => 'WeaponGun.Gun',
                        'HardpointName' => 'hardpoint_weapon_right',
                        'ClassName' => 'KSAR_BallisticGatling_S2',
                    ],
                ],
            ],
        ]);

    $response = $this->getJson("/api/v3/vehicles/{$vehicle->uuid}");

    $response->assertSuccessful()
        ->assertJsonPath('data.weapon_snapshot.pilot_guns_count', 3)
        ->assertJsonPath('data.weapon_snapshot.turrets_manned_count', 0)
        ->assertJsonPath('data.weapon_snapshot.turrets_remote_count', 0)
        ->assertJsonPath('data.weapon_snapshot.turret_weapon_guns_count', 0)
        ->assertJsonPath('data.weapon_snapshot.missile_rack_count', 0)
        ->assertJsonPath('data.weapon_snapshot.missile_count', 0)
        ->assertJsonPath('data.weapon_snapshot.countermeasures_count', 0);
});

it('includes weapon snapshot on v2 show route', function (): void {
    $vehicle = Vehicle::factory()->create();

    VehicleData::factory()
        ->for($vehicle)
        ->for($this->gameVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'name' => 'Test Fighter',
            'class_name' => 'TEST_Fighter',
            'data' => [
                'Loadout' => [
                    [
                        'Type' => 'WeaponGun.Gun',
                        'HardpointName' => 'hardpoint_weapon_nose',
                        'ClassName' => 'KLWE_LaserRepeater_S4',
                    ],
                ],
            ],
        ]);

    $response = $this->getJson("/api/v2/vehicles/{$vehicle->uuid}");

    $response->assertSuccessful()
        ->assertJsonPath('data.weapon_snapshot.pilot_guns_count', 1)
        ->assertJsonPath('data.weapon_snapshot.turrets_manned_count', 0)
        ->assertJsonPath('data.weapon_snapshot.turrets_remote_count', 0)
        ->assertJsonPath('data.weapon_snapshot.turret_weapon_guns_count', 0)
        ->assertJsonPath('data.weapon_snapshot.missile_rack_count', 0)
        ->assertJsonPath('data.weapon_snapshot.missile_count', 0)
        ->assertJsonPath('data.weapon_snapshot.countermeasures_count', 0);
});

it('excludes weapon snapshot from index route', function (): void {
    $vehicle = Vehicle::factory()->create();

    VehicleData::factory()
        ->for($vehicle)
        ->for($this->gameVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'name' => 'Test Fighter',
            'class_name' => 'TEST_Fighter',
            'data' => [
                'Loadout' => [
                    [
                        'Type' => 'WeaponGun.Gun',
                        'HardpointName' => 'hardpoint_weapon_nose',
                        'ClassName' => 'KLWE_LaserRepeater_S4',
                    ],
                ],
            ],
        ]);

    $response = $this->getJson('/api/v3/vehicles');

    $response->assertSuccessful();

    $json = $response->json();
    expect($json['data'])->toBeArray();

    if (count($json['data']) > 0) {
        expect($json['data'][0])->not->toHaveKey('weapon_snapshot');
    }
});

it('returns null weapon snapshot when loadout is empty', function (): void {
    $vehicle = Vehicle::factory()->create();

    VehicleData::factory()
        ->for($vehicle)
        ->for($this->gameVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'name' => 'Test Vehicle',
            'class_name' => 'TEST_Vehicle',
            'data' => [
                'Loadout' => [],
            ],
        ]);

    $response = $this->getJson("/api/v3/vehicles/{$vehicle->uuid}");

    $response->assertSuccessful();

    $json = $response->json('data');
    expect($json)->not->toHaveKey('weapon_snapshot');
});

it('computes correct weapon snapshot for 300i-like vehicle', function (): void {
    $vehicle = Vehicle::factory()->create();

    VehicleData::factory()
        ->for($vehicle)
        ->for($this->gameVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'name' => 'Test 300i',
            'class_name' => 'TEST_300i',
            'data' => [
                'Loadout' => [
                    [
                        'Type' => 'WeaponGun.Gun',
                        'HardpointName' => 'hardpoint_weapon_1',
                        'ClassName' => 'Gun_1',
                    ],
                    [
                        'Type' => 'WeaponGun.Gun',
                        'HardpointName' => 'hardpoint_weapon_2',
                        'ClassName' => 'Gun_2',
                    ],
                    [
                        'Type' => 'WeaponGun.Gun',
                        'HardpointName' => 'hardpoint_weapon_3',
                        'ClassName' => 'Gun_3',
                    ],
                    [
                        'Type' => 'MissileLauncher.MissileRack',
                        'HardpointName' => 'hardpoint_missile_left',
                        'ClassName' => 'MissileRack_S2',
                        'Loadout' => [
                            [
                                'Type' => 'Missile.Missile',
                                'HardpointName' => 'missile_1',
                                'ClassName' => 'Missile_S2',
                            ],
                        ],
                    ],
                    [
                        'Type' => 'MissileLauncher.MissileRack',
                        'HardpointName' => 'hardpoint_missile_right',
                        'ClassName' => 'MissileRack_S2',
                        'Loadout' => [
                            [
                                'Type' => 'Missile.Missile',
                                'HardpointName' => 'missile_2',
                                'ClassName' => 'Missile_S2',
                            ],
                        ],
                    ],
                    [
                        'Type' => 'WeaponDefensive.CountermeasureLauncher',
                        'HardpointName' => 'hardpoint_countermeasure_left',
                        'ClassName' => 'CountermeasureLauncher',
                    ],
                    [
                        'Type' => 'WeaponDefensive.CountermeasureLauncher',
                        'HardpointName' => 'hardpoint_countermeasure_right',
                        'ClassName' => 'CountermeasureLauncher',
                    ],
                ],
            ],
        ]);

    $response = $this->getJson("/api/v3/vehicles/{$vehicle->uuid}");

    $response->assertSuccessful()
        ->assertJsonPath('data.weapon_snapshot.pilot_guns_count', 3)
        ->assertJsonPath('data.weapon_snapshot.turrets_manned_count', 0)
        ->assertJsonPath('data.weapon_snapshot.turrets_remote_count', 0)
        ->assertJsonPath('data.weapon_snapshot.turret_weapon_guns_count', 0)
        ->assertJsonPath('data.weapon_snapshot.missile_rack_count', 2)
        ->assertJsonPath('data.weapon_snapshot.missile_count', 2)
        ->assertJsonPath('data.weapon_snapshot.countermeasures_count', 2);
});

it('computes correct weapon snapshot for Ballista-like vehicle', function (): void {
    $vehicle = Vehicle::factory()->create();

    VehicleData::factory()
        ->for($vehicle)
        ->for($this->gameVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'name' => 'Test Ballista',
            'class_name' => 'TEST_Ballista',
            'data' => [
                'Loadout' => [
                    [
                        'Type' => 'Turret.RemoteTurret',
                        'HardpointName' => 'hardpoint_turret_left',
                        'ClassName' => 'Ballista_Remote_Turret_Left',
                        'Loadout' => [
                            [
                                'Type' => 'WeaponGun.Gun',
                                'HardpointName' => 'hardpoint_gun_left',
                                'ClassName' => 'Gun_Left',
                            ],
                        ],
                    ],
                    [
                        'Type' => 'Turret.RemoteTurret',
                        'HardpointName' => 'hardpoint_turret_right',
                        'ClassName' => 'Ballista_Remote_Turret_Right',
                        'Loadout' => [
                            [
                                'Type' => 'WeaponGun.Gun',
                                'HardpointName' => 'hardpoint_gun_right',
                                'ClassName' => 'Gun_Right',
                            ],
                        ],
                    ],
                    [
                        'Type' => 'MissileLauncher.MissileRack',
                        'HardpointName' => 'hardpoint_missile_rack_1',
                        'ClassName' => 'MissileRack_1',
                        'Loadout' => [
                            [
                                'Type' => 'Missile.Missile',
                                'HardpointName' => 'missile_1_1',
                                'ClassName' => 'Missile',
                            ],
                            [
                                'Type' => 'Missile.Missile',
                                'HardpointName' => 'missile_1_2',
                                'ClassName' => 'Missile',
                            ],
                        ],
                    ],
                    [
                        'Type' => 'MissileLauncher.MissileRack',
                        'HardpointName' => 'hardpoint_missile_rack_2',
                        'ClassName' => 'MissileRack_2',
                        'Loadout' => [
                            [
                                'Type' => 'Missile.Missile',
                                'HardpointName' => 'missile_2_1',
                                'ClassName' => 'Missile',
                            ],
                            [
                                'Type' => 'Missile.Missile',
                                'HardpointName' => 'missile_2_2',
                                'ClassName' => 'Missile',
                            ],
                            [
                                'Type' => 'Missile.Missile',
                                'HardpointName' => 'missile_2_3',
                                'ClassName' => 'Missile',
                            ],
                        ],
                    ],
                    [
                        'Type' => 'MissileLauncher.MissileRack',
                        'HardpointName' => 'hardpoint_missile_rack_3',
                        'ClassName' => 'MissileRack_3',
                        'Loadout' => [
                            [
                                'Type' => 'Missile.Missile',
                                'HardpointName' => 'missile_3_1',
                                'ClassName' => 'Missile',
                            ],
                            [
                                'Type' => 'Missile.Missile',
                                'HardpointName' => 'missile_3_2',
                                'ClassName' => 'Missile',
                            ],
                        ],
                    ],
                    [
                        'Type' => 'MissileLauncher.MissileRack',
                        'HardpointName' => 'hardpoint_missile_rack_4',
                        'ClassName' => 'MissileRack_4',
                        'Loadout' => [
                            [
                                'Type' => 'Missile.Missile',
                                'HardpointName' => 'missile_4_1',
                                'ClassName' => 'Missile',
                            ],
                            [
                                'Type' => 'Missile.Missile',
                                'HardpointName' => 'missile_4_2',
                                'ClassName' => 'Missile',
                            ],
                            [
                                'Type' => 'Missile.Missile',
                                'HardpointName' => 'missile_4_3',
                                'ClassName' => 'Missile',
                            ],
                        ],
                    ],
                ],
            ],
        ]);

    $response = $this->getJson("/api/v3/vehicles/{$vehicle->uuid}");

    $response->assertSuccessful()
        ->assertJsonPath('data.weapon_snapshot.pilot_guns_count', 0)
        ->assertJsonPath('data.weapon_snapshot.turrets_manned_count', 0)
        ->assertJsonPath('data.weapon_snapshot.turrets_remote_count', 2)
        ->assertJsonPath('data.weapon_snapshot.turret_weapon_guns_count', 2)
        ->assertJsonPath('data.weapon_snapshot.missile_rack_count', 4)
        ->assertJsonPath('data.weapon_snapshot.missile_count', 10)
        ->assertJsonPath('data.weapon_snapshot.countermeasures_count', 0);
});

it('computes correct weapon snapshot for HoverQuad-like vehicle', function (): void {
    $vehicle = Vehicle::factory()->create();

    VehicleData::factory()
        ->for($vehicle)
        ->for($this->gameVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'name' => 'Test HoverQuad',
            'class_name' => 'TEST_HoverQuad',
            'data' => [
                'Loadout' => [
                    [
                        'Type' => 'WeaponDefensive.CountermeasureLauncher',
                        'HardpointName' => 'hardpoint_countermeasure_left',
                        'ClassName' => 'CountermeasureLauncher',
                    ],
                    [
                        'Type' => 'WeaponDefensive.CountermeasureLauncher',
                        'HardpointName' => 'hardpoint_countermeasure_right',
                        'ClassName' => 'CountermeasureLauncher',
                    ],
                ],
            ],
        ]);

    $response = $this->getJson("/api/v3/vehicles/{$vehicle->uuid}");

    $response->assertSuccessful()
        ->assertJsonPath('data.weapon_snapshot.pilot_guns_count', 0)
        ->assertJsonPath('data.weapon_snapshot.turrets_manned_count', 0)
        ->assertJsonPath('data.weapon_snapshot.turrets_remote_count', 0)
        ->assertJsonPath('data.weapon_snapshot.turret_weapon_guns_count', 0)
        ->assertJsonPath('data.weapon_snapshot.missile_rack_count', 0)
        ->assertJsonPath('data.weapon_snapshot.missile_count', 0)
        ->assertJsonPath('data.weapon_snapshot.countermeasures_count', 2);
});
