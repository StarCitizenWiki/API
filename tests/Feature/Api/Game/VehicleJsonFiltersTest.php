<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\Game\Vehicle;
use App\Models\Game\VehicleData;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('filters vehicles by json speed scm', function (): void {
    if (config('database.default') === 'sqlite') {
        $this->markTestSkipped('SQLite does not support JSON path operators used for vehicle filters.');
    }

    $version = GameVersion::factory()->create([
        'code' => '3.25.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $matchingVehicle = Vehicle::factory()->create();
    VehicleData::factory()
        ->for($matchingVehicle)
        ->for($version, 'gameVersion')
        ->create([
            'data' => [
                'FlightCharacteristics' => [
                    'Speeds' => [
                        'Scm' => 123,
                    ],
                ],
            ],
        ]);

    $otherVehicle = Vehicle::factory()->create();
    VehicleData::factory()
        ->for($otherVehicle)
        ->for($version, 'gameVersion')
        ->create([
            'data' => [
                'FlightCharacteristics' => [
                    'Speeds' => [
                        'Scm' => 200,
                    ],
                ],
            ],
        ]);

    $response = $this->getJson('/api/vehicles?filter[speed.scm]=123');

    $response->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.uuid', $matchingVehicle->uuid);
});

it('sorts vehicles by json length', function (): void {
    if (config('database.default') === 'sqlite') {
        $this->markTestSkipped('SQLite does not support JSON path operators used for vehicle sorting.');
    }

    $version = GameVersion::factory()->create([
        'code' => '3.25.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $shortVehicle = Vehicle::factory()->create();
    VehicleData::factory()
        ->for($shortVehicle)
        ->for($version, 'gameVersion')
        ->create([
            'data' => [
                'Length' => 10,
            ],
        ]);

    $longVehicle = Vehicle::factory()->create();
    VehicleData::factory()
        ->for($longVehicle)
        ->for($version, 'gameVersion')
        ->create([
            'data' => [
                'Length' => 20,
            ],
        ]);

    $response = $this->getJson('/api/vehicles?sort=Length');

    $response->assertSuccessful()
        ->assertJsonPath('data.0.uuid', $shortVehicle->uuid)
        ->assertJsonPath('data.1.uuid', $longVehicle->uuid);
});
