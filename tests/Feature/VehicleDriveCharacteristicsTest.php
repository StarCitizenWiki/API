<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\Game\Manufacturer;
use App\Models\Game\Vehicle;
use App\Models\Game\VehicleData;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

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

// --- API Tests ---

it('returns drive data for PhysicalWheeled ground vehicles', function (): void {
    $vehicle = Vehicle::factory()->create();

    VehicleData::factory()
        ->for($vehicle)
        ->for($this->gameVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'is_vehicle' => true,
            'is_spaceship' => false,
            'data' => [
                'DriveCharacteristics' => [
                    'IsTracked' => false,
                    'Speed' => [
                        'WheelMaxSpeedKph' => 47.07,
                        'WheelMaxSpeedMs' => 13.08,
                        'ReverseSpeedKph' => 20.17,
                        'ReverseSpeedMs' => 5.60,
                    ],
                    'Wheels' => [
                        'Count' => 4,
                        'DrivingCount' => 4,
                        'SteeringCount' => 2,
                        'DriveType' => 'AWD',
                    ],
                    'Agility' => [
                        'HandlingScore' => 0.5,
                        'GripScore' => 0.1875,
                        'AccelerationScore' => 1.0,
                    ],
                ],
            ],
        ]);

    $response = $this->getJson(route('vehicles.show', ['vehicle' => $vehicle->uuid]));

    $response->assertOk();
    $response->assertJsonPath('data.drive.max_speed_kph', 47.07);
    $response->assertJsonPath('data.drive.max_speed_ms', 13.08);
    $response->assertJsonPath('data.drive.reverse_speed_kph', 20.17);
    $response->assertJsonPath('data.drive.reverse_speed_ms', 5.60);
    $response->assertJsonPath('data.drive.is_tracked', false);
    $response->assertJsonPath('data.drive.wheels.count', 4);
    $response->assertJsonPath('data.drive.wheels.driving_count', 4);
    $response->assertJsonPath('data.drive.wheels.steering_count', 2);
    $response->assertJsonPath('data.drive.wheels.drive_type', 'AWD');
    $response->assertJsonPath('data.drive.agility.handling', 0.5);
    $response->assertJsonPath('data.drive.agility.grip', 0.1875);
    $response->assertJsonPath('data.drive.agility.acceleration', 1);
});

it('returns drive data for TrackWheeled vehicles using TrackMaxSpeedKph', function (): void {
    $vehicle = Vehicle::factory()->create();

    VehicleData::factory()
        ->for($vehicle)
        ->for($this->gameVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'is_vehicle' => true,
            'is_spaceship' => false,
            'data' => [
                'DriveCharacteristics' => [
                    'IsTracked' => true,
                    'Speed' => [
                        'TrackMaxSpeedKph' => 90,
                        'TrackMaxSpeedMs' => 25,
                    ],
                    'Wheels' => [
                        'Count' => 22,
                        'DrivingCount' => 2,
                        'SteeringCount' => 0,
                        'DriveType' => 'RWD',
                    ],
                    'Agility' => [
                        'HandlingScore' => 0,
                        'GripScore' => 0.3125,
                        'AccelerationScore' => 0.091,
                    ],
                ],
            ],
        ]);

    $response = $this->getJson(route('vehicles.show', ['vehicle' => $vehicle->uuid]));

    $response->assertOk();
    $response->assertJsonPath('data.drive.max_speed_kph', 90);
    $response->assertJsonPath('data.drive.max_speed_ms', 25);
    $response->assertJsonPath('data.drive.is_tracked', true);
    $response->assertJsonPath('data.drive.wheels.drive_type', 'RWD');
    $response->assertJsonPath('data.drive.agility.handling', 0);
    $response->assertJsonPath('data.drive.agility.grip', 0.3125);
    $response->assertJsonPath('data.drive.agility.acceleration', 0.091);
});

it('returns drive data for ArcadeWheeled vehicles with reverse speed', function (): void {
    $vehicle = Vehicle::factory()->create();

    VehicleData::factory()
        ->for($vehicle)
        ->for($this->gameVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'is_vehicle' => true,
            'is_spaceship' => false,
            'data' => [
                'DriveCharacteristics' => [
                    'IsTracked' => false,
                    'Speed' => [
                        'TopSpeedKph' => 115.2,
                        'TopSpeedMs' => 32,
                        'ReverseSpeedKph' => 25.2,
                        'ReverseSpeedMs' => 7,
                    ],
                    'Wheels' => [
                        'Count' => 6,
                        'DrivingCount' => 0,
                        'SteeringCount' => 6,
                        'DriveType' => 'Unknown',
                    ],
                    'Agility' => [
                        'HandlingScore' => 1,
                        'GripScore' => 0.25,
                        'AccelerationScore' => 0.4,
                    ],
                ],
            ],
        ]);

    $response = $this->getJson(route('vehicles.show', ['vehicle' => $vehicle->uuid]));

    $response->assertOk();
    $response->assertJsonPath('data.drive.max_speed_kph', 115.2);
    $response->assertJsonPath('data.drive.max_speed_ms', 32);
    $response->assertJsonPath('data.drive.reverse_speed_kph', 25.2);
    $response->assertJsonPath('data.drive.reverse_speed_ms', 7);
    $response->assertJsonPath('data.drive.wheels.drive_type', 'Unknown');
    $response->assertJsonPath('data.drive.agility.handling', 1);
    $response->assertJsonPath('data.drive.agility.grip', 0.25);
    $response->assertJsonPath('data.drive.agility.acceleration', 0.4);
});

it('falls back to old Tracks.IsTracked path when top-level IsTracked is missing', function (): void {
    $vehicle = Vehicle::factory()->create();

    VehicleData::factory()
        ->for($vehicle)
        ->for($this->gameVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'is_vehicle' => true,
            'is_spaceship' => false,
            'data' => [
                'DriveCharacteristics' => [
                    'Speed' => [
                        'WheelMaxSpeedKph' => 100.0,
                        'WheelMaxSpeedMs' => 27.78,
                    ],
                    'Tracks' => [
                        'IsTracked' => true,
                    ],
                ],
            ],
        ]);

    $response = $this->getJson(route('vehicles.show', ['vehicle' => $vehicle->uuid]));

    $response->assertOk();
    $response->assertJsonPath('data.drive.is_tracked', true);
});

it('does not return drive data for spaceships', function (): void {
    $vehicle = Vehicle::factory()->create();

    VehicleData::factory()
        ->for($vehicle)
        ->for($this->gameVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'is_vehicle' => false,
            'is_spaceship' => true,
            'data' => [],
        ]);

    $response = $this->getJson(route('vehicles.show', ['vehicle' => $vehicle->uuid]));

    $response->assertOk();
    $response->assertJsonMissingPath('data.drive');
});

it('does not return friction or suspension in drive data', function (): void {
    $vehicle = Vehicle::factory()->create();

    VehicleData::factory()
        ->for($vehicle)
        ->for($this->gameVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'is_vehicle' => true,
            'is_spaceship' => false,
            'data' => [
                'DriveCharacteristics' => [
                    'IsTracked' => false,
                    'Speed' => [
                        'WheelMaxSpeedKph' => 100.0,
                        'WheelMaxSpeedMs' => 27.78,
                    ],
                    'Wheels' => [
                        'Friction' => [
                            'MaxFrictionAverage' => 1.5,
                            'MinFrictionAverage' => 1.0,
                        ],
                        'Suspension' => [
                            'SuspensionLengthMeters' => 0.125,
                            'MaxExtensionMeters' => 0.2,
                        ],
                    ],
                ],
            ],
        ]);

    $response = $this->getJson(route('vehicles.show', ['vehicle' => $vehicle->uuid]));

    $response->assertOk();
    $response->assertJsonPath('data.drive.max_speed_kph', 100);
    $response->assertJsonMissingPath('data.drive.friction');
    $response->assertJsonMissingPath('data.drive.suspension');
});

// --- Blade Tests ---

it('renders drive characteristics card on ground vehicle page', function (): void {
    $vehicle = Vehicle::factory()->create([
        'slug' => 'test-ground-vehicle',
        'uuid' => '11111111-1111-4111-8111-111111111111',
    ]);

    VehicleData::factory()
        ->for($vehicle)
        ->for($this->gameVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'name' => 'Test Ground Vehicle',
            'display_name' => 'Test Ground Vehicle',
            'is_vehicle' => true,
            'is_spaceship' => false,
            'data' => [
                'DriveCharacteristics' => [
                    'IsTracked' => false,
                    'Speed' => [
                        'WheelMaxSpeedKph' => 47.07,
                        'WheelMaxSpeedMs' => 13.08,
                        'ReverseSpeedKph' => 20.17,
                        'ReverseSpeedMs' => 5.60,
                    ],
                    'Wheels' => [
                        'Count' => 4,
                        'DrivingCount' => 4,
                        'SteeringCount' => 2,
                        'DriveType' => 'AWD',
                    ],
                    'Agility' => [
                        'HandlingScore' => 0.5,
                        'GripScore' => 0.1875,
                        'AccelerationScore' => 1.0,
                    ],
                ],
            ],
        ]);

    $response = $this->get(route('web.vehicles.show', $vehicle->uuid));

    $response->assertOk()
        ->assertSeeText('Drive Characteristics')
        ->assertSeeText('47.1 km/h')
        ->assertSeeText('20.2 km/h')
        ->assertSeeText('Wheeled')
        ->assertSeeText('AWD'); // acceleration
});

it('renders tracked label for tracked vehicles', function (): void {
    $vehicle = Vehicle::factory()->create([
        'slug' => 'test-tracked-vehicle',
        'uuid' => '22222222-2222-4222-8222-222222222222',
    ]);

    VehicleData::factory()
        ->for($vehicle)
        ->for($this->gameVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'name' => 'Test Tracked Vehicle',
            'display_name' => 'Test Tracked Vehicle',
            'is_vehicle' => true,
            'is_spaceship' => false,
            'data' => [
                'DriveCharacteristics' => [
                    'IsTracked' => true,
                    'Speed' => [
                        'TrackMaxSpeedKph' => 90,
                        'TrackMaxSpeedMs' => 25,
                    ],
                    'Wheels' => [
                        'Count' => 22,
                        'DrivingCount' => 2,
                        'SteeringCount' => 0,
                        'DriveType' => 'RWD',
                    ],
                    'Agility' => [
                        'HandlingScore' => 0,
                        'GripScore' => 0.3125,
                        'AccelerationScore' => 0.091,
                    ],
                ],
            ],
        ]);

    $response = $this->get(route('web.vehicles.show', $vehicle->uuid));

    $response->assertOk()
        ->assertSeeText('Tracked')
        ->assertSeeText('RWD')
        ->assertDontSeeText('Wheeled');
});

it('hides propulsion card for ground vehicles without quantum drive', function (): void {
    $vehicle = Vehicle::factory()->create([
        'slug' => 'test-no-propulsion-vehicle',
        'uuid' => '33333333-3333-4333-8333-333333333333',
    ]);

    VehicleData::factory()
        ->for($vehicle)
        ->for($this->gameVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'name' => 'No Propulsion Vehicle',
            'display_name' => 'No Propulsion Vehicle',
            'is_vehicle' => true,
            'is_spaceship' => false,
            'data' => [
                'DriveCharacteristics' => [
                    'IsTracked' => false,
                    'Speed' => [
                        'WheelMaxSpeedKph' => 100.0,
                        'WheelMaxSpeedMs' => 27.78,
                    ],
                ],
            ],
        ]);

    $response = $this->get(route('web.vehicles.show', $vehicle->uuid));

    $response->assertOk()
        ->assertDontSeeText('Fuel & Quantum');
});

it('shows reverse speed on the drive card for ArcadeWheeled vehicles', function (): void {
    $vehicle = Vehicle::factory()->create([
        'slug' => 'test-mule-vehicle',
        'uuid' => '44444444-4444-4444-8444-444444444444',
    ]);

    VehicleData::factory()
        ->for($vehicle)
        ->for($this->gameVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'name' => 'Drake Mule',
            'display_name' => 'Drake Mule',
            'is_vehicle' => true,
            'is_spaceship' => false,
            'data' => [
                'DriveCharacteristics' => [
                    'IsTracked' => false,
                    'Speed' => [
                        'TopSpeedKph' => 115.2,
                        'TopSpeedMs' => 32,
                        'ReverseSpeedKph' => 25.2,
                        'ReverseSpeedMs' => 7,
                    ],
                    'Wheels' => [
                        'Count' => 6,
                        'DrivingCount' => 0,
                        'SteeringCount' => 6,
                        'DriveType' => 'Unknown',
                    ],
                    'Agility' => [
                        'HandlingScore' => 1,
                        'GripScore' => 0.25,
                        'AccelerationScore' => 0.4,
                    ],
                ],
            ],
        ]);

    $response = $this->get(route('web.vehicles.show', $vehicle->uuid));

    $response->assertOk()
        ->assertSeeText('Reverse')
        ->assertSeeText('25.2 km/h')
        ->assertSeeText('Unknown');
});
