<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\Game\Manufacturer;
use App\Models\Game\Vehicle;
use App\Models\Game\VehicleData;

beforeEach(function (): void {
    $this->manufacturer = Manufacturer::factory()->create([
        'name' => 'Test Manufacturer',
        'code' => 'TEST',
    ]);

    $this->gameVersion = GameVersion::factory()->create([
        'code' => '4.2.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);
});

describe('no_fuel_params', function (): void {
    it('returns no_fuel_params when present in IFCS data', function (): void {
        $vehicle = Vehicle::factory()->create();

        VehicleData::factory()
            ->for($vehicle)
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'is_spaceship' => true,
                'data' => [
                    'FlightCharacteristics' => [
                        'IFCS' => [
                            'NoFuelParams' => [
                                'LinearAccelerationModifier' => 0.1,
                                'AngularAccelerationModifier' => 0.1,
                                'AngularVelocityModifier' => 0.1,
                                'LegacyMaxSpeed' => 20,
                            ],
                        ],
                    ],
                ],
            ]);

        $response = $this->getJson(route('vehicles.show', ['vehicle' => $vehicle->uuid]));

        $response->assertOk();
        $response->assertJsonPath('data.no_fuel_params.linear_acceleration_modifier', 0.1);
        $response->assertJsonPath('data.no_fuel_params.angular_acceleration_modifier', 0.1);
        $response->assertJsonPath('data.no_fuel_params.angular_velocity_modifier', 0.1);
        $response->assertJsonPath('data.no_fuel_params.legacy_max_speed', 20);
    });

    it('omits no_fuel_params when IFCS data has no NoFuelParams', function (): void {
        $vehicle = Vehicle::factory()->create();

        VehicleData::factory()
            ->for($vehicle)
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'is_spaceship' => true,
                'data' => [
                    'FlightCharacteristics' => [
                        'IFCS' => [
                            'ScmSpeed' => 200,
                        ],
                    ],
                ],
            ]);

        $response = $this->getJson(route('vehicles.show', ['vehicle' => $vehicle->uuid]));

        $response->assertOk();
        $response->assertJsonMissingPath('data.no_fuel_params');
    });

    it('omits no_fuel_params when FlightCharacteristics is missing', function (): void {
        $vehicle = Vehicle::factory()->create();

        VehicleData::factory()
            ->for($vehicle)
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'is_spaceship' => true,
                'data' => [],
            ]);

        $response = $this->getJson(route('vehicles.show', ['vehicle' => $vehicle->uuid]));

        $response->assertOk();
        $response->assertJsonMissingPath('data.no_fuel_params');
    });
});

describe('insurance', function (): void {
    it('returns insurance fields from data', function (): void {
        $vehicle = Vehicle::factory()->create();

        VehicleData::factory()
            ->for($vehicle)
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'is_spaceship' => true,
                'data' => [
                    'Insurance' => [
                        'StandardClaimTime' => 4.86,
                        'ExpeditedClaimTime' => 1.62,
                        'ExpeditedCost' => 2658,
                    ],
                ],
            ]);

        $response = $this->getJson(route('vehicles.show', ['vehicle' => $vehicle->uuid]));

        $response->assertOk();
        $response->assertJsonPath('data.insurance.claim_time', 4.86);
        $response->assertJsonPath('data.insurance.expedite_time', 1.62);
        $response->assertJsonPath('data.insurance.expedite_cost', 2658);
    });
});
