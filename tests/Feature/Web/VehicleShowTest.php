<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\Game\Manufacturer;
use App\Models\Game\Vehicle;
use App\Models\Game\VehicleData;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders nested hardpoints on the vehicle page', function (): void {
    $version = GameVersion::factory()->create([
        'code' => '4.2.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $manufacturer = Manufacturer::factory()->create([
        'name' => 'Aegis Dynamics',
        'code' => 'AEGS',
    ]);

    $vehicle = Vehicle::factory()->create([
        'uuid' => '7e3b7dd7-7a1b-4dcf-8d7b-7b7bcb3f8b76',
    ]);

    VehicleData::factory()
        ->for($vehicle)
        ->for($version, 'gameVersion')
        ->for($manufacturer)
        ->create([
            'shipmatrix_id' => null,
            'name' => 'Test Vehicle',
            'display_name' => 'Test Vehicle',
            'class_name' => 'TEST_VEHICLE',
            'career' => 'Exploration',
            'role' => 'Scout',
            'is_vehicle' => true,
            'is_gravlev' => false,
            'is_spaceship' => true,
            'size' => 3,
            'data' => [
                'Length' => 20,
                'Width' => 10,
                'Height' => 5,
                'Mass' => 10000,
                'MassLoadout' => 12000,
                'MassTotal' => 22000,
                'CrossSection' => [
                    'X' => 8,
                    'Y' => 6,
                    'Z' => 4,
                ],
                'Insurance' => [
                    'StandardClaimTime' => 12.5,
                    'ExpeditedClaimTime' => 4.5,
                    'ExpeditedCost' => 1250,
                ],
                'Loadout' => [
                    [
                        'HardpointName' => 'S1',
                        'Type' => 'Weapon.Turret',
                        'MinSize' => 1,
                        'MaxSize' => 1,
                        'Loadout' => [
                            [
                                'HardpointName' => 'S1-1',
                                'Type' => 'Weapon.Gun',
                                'MinSize' => 1,
                                'MaxSize' => 1,
                                'Loadout' => [],
                            ],
                        ],
                    ],
                ],
                'MannedTurrets' => [
                    [
                        'Size' => 2,
                        'Turret' => true,
                        'Fixed' => true,
                        'WeaponSizes' => [2, 2],
                    ],
                ],
                'RemoteTurrets' => [
                    [
                        'Size' => 3,
                        'Gimballed' => true,
                        'WeaponSizes' => [1, 1],
                    ],
                ],
                'CargoGrids' => [
                    [
                        'SCU' => 8,
                        'X' => 2,
                        'Y' => 4,
                        'Z' => 1,
                        'IsOpenContainer' => true,
                    ],
                ],
            ],
        ]);

    $response = $this->get(route('web.vehicles.show', $vehicle->uuid));

    $response->assertStatus(200)
        ->assertSeeText('Combat & Systems')
        ->assertSeeText('Turrets')
        ->assertSeeText('Manned')
        ->assertSeeText('Remote')
        ->assertSeeText('Test Vehicle')
        ->assertSeeText('Gimballed')
        ->assertSeeText('Fixed')
        ->assertSeeText('Dimensions & Mass')
        ->assertSeeText('Cargo Grids')
        ->assertSeeText('Insurance')
        ->assertSeeText('Expedite')
        ->assertSeeText('Open')
        ->assertSeeText('S1')
        ->assertSeeText('S1 1');
});
