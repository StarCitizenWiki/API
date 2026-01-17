<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\Game\Manufacturer;
use App\Models\Game\Vehicle;
use App\Models\Game\VehicleData;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders the vehicle show view with api data', function (): void {
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
        'uuid' => '7e3b7dd7-7a1b-4dcf-8d7b-7b7bcb3f8b70',
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
                'MassTotal' => 15000,
                'Cargo' => 12,
                'Stowage' => 4,
                'Crew' => 2,
                'WeaponCrew' => 1,
                'Health' => 2500,
                'ShieldsTotal' => [
                    'Hp' => 5000,
                ],
                'ShieldController' => [
                    'FaceType' => 'FourFaces',
                ],
                'FlightCharacteristics' => [
                    'Speeds' => [
                        'Scm' => 200,
                        'Max' => 900,
                    ],
                    'AngularRates' => [
                        'Pitch' => 30,
                        'Yaw' => 25,
                        'Roll' => 120,
                    ],
                    'Acceleration' => [
                        'Raw' => [
                            'Forward' => 10,
                            'Backward' => 5,
                        ],
                    ],
                ],
                'Propulsion' => [
                    'FuelCapacity' => 1000,
                ],
                'QuantumTravel' => [
                    'Speed' => 100000,
                    'Range' => 500000,
                ],
                'Parts' => [
                    [
                        'Name' => 'NOSE',
                        'DamageMax' => 100,
                        'Children' => [
                            [
                                'Name' => 'NOSE_CAP',
                                'DamageMax' => 50,
                            ],
                        ],
                    ],
                ],
            ],
        ]);

    $response = $this->get(route('web.vehicles.show', $vehicle->uuid));

    $response->assertOk()
        ->assertViewIs('vehicles.show')
        ->assertSee('Test Vehicle')
        ->assertSee('Overview')
        ->assertSee('Ports & Hardpoints', false)
        ->assertSee('Nose cap')
        ->assertSee('All Data');
});
