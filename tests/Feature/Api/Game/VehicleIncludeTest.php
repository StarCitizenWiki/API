<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\Game\Vehicle;
use App\Models\Game\VehicleData;

beforeEach(function (): void {
    $this->defaultVersion = GameVersion::factory()->create(['is_default' => true]);
});

it('accepts hardpoints include on vehicle show route', function (): void {
    $vehicle = Vehicle::factory()->create();
    VehicleData::factory()->create([
        'vehicle_id' => $vehicle->id,
        'game_version_id' => $this->defaultVersion->id,
        'name' => 'Test Ship',
        'display_name' => 'Test Ship',
    ]);

    $response = $this->getJson("/api/vehicles/{$vehicle->slug}?include=hardpoints");

    $response->assertSuccessful()
        ->assertJsonPath('data.name', 'Test Ship')
        ->assertJsonStructure(['data' => ['ports']]);
});

it('includes ports on vehicle show route', function (): void {
    $vehicle = Vehicle::factory()->create();
    VehicleData::factory()->create([
        'vehicle_id' => $vehicle->id,
        'game_version_id' => $this->defaultVersion->id,
        'name' => 'Test Ship',
        'display_name' => 'Test Ship',
    ]);

    $response = $this->getJson("/api/vehicles/{$vehicle->slug}?include=ports");

    $response->assertSuccessful()
        ->assertJsonPath('data.name', 'Test Ship')
        ->assertJsonStructure(['data' => ['ports']]);
});

it('accepts components include on vehicle show route without error', function (): void {
    $vehicle = Vehicle::factory()->create();
    VehicleData::factory()->create([
        'vehicle_id' => $vehicle->id,
        'game_version_id' => $this->defaultVersion->id,
        'name' => 'Test Ship',
        'display_name' => 'Test Ship',
    ]);

    $response = $this->getJson("/api/vehicles/{$vehicle->slug}?include=components");

    $response->assertSuccessful()
        ->assertJsonPath('data.name', 'Test Ship')
        ->assertJsonStructure(['data' => ['uuid', 'name', 'link']]);
});

it('accepts hardpoints include on vehicle index route', function (): void {
    $vehicle = Vehicle::factory()->create();
    VehicleData::factory()->create([
        'vehicle_id' => $vehicle->id,
        'game_version_id' => $this->defaultVersion->id,
        'name' => 'Test Ship',
        'display_name' => 'Test Ship',
    ]);

    $response = $this->getJson('/api/vehicles?include=hardpoints');

    $response->assertSuccessful();
    expect($response->json('data'))->toHaveCount(1)
        ->and($response->json('data.0.name'))->toBe('Test Ship');
});

it('accepts ports include on vehicle index route', function (): void {
    $vehicle = Vehicle::factory()->create();
    VehicleData::factory()->create([
        'vehicle_id' => $vehicle->id,
        'game_version_id' => $this->defaultVersion->id,
        'name' => 'Test Ship',
        'display_name' => 'Test Ship',
    ]);

    $response = $this->getJson('/api/vehicles?include=ports');

    $response->assertSuccessful();
    expect($response->json('data'))->toHaveCount(1)
        ->and($response->json('data.0.name'))->toBe('Test Ship');
});
