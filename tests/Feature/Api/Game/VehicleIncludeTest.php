<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\Game\Vehicle;
use App\Models\Game\VehicleData;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->defaultVersion = GameVersion::factory()->create(['is_default' => true]);
});

it('returns 200 when including hardpoints on vehicle show route', function (): void {
    $vehicle = Vehicle::factory()->create();
    VehicleData::factory()->create([
        'vehicle_id' => $vehicle->id,
        'game_version_id' => $this->defaultVersion->id,
        'name' => 'Test Ship',
    ]);

    $response = $this->getJson("/api/vehicles/{$vehicle->slug}?include=hardpoints");

    $response->assertSuccessful();
});

it('returns 200 when including ports on vehicle show route', function (): void {
    $vehicle = Vehicle::factory()->create();
    VehicleData::factory()->create([
        'vehicle_id' => $vehicle->id,
        'game_version_id' => $this->defaultVersion->id,
        'name' => 'Test Ship',
    ]);

    $response = $this->getJson("/api/vehicles/{$vehicle->slug}?include=ports");

    $response->assertSuccessful();
});

it('returns 200 when including components on vehicle show route', function (): void {
    $vehicle = Vehicle::factory()->create();
    VehicleData::factory()->create([
        'vehicle_id' => $vehicle->id,
        'game_version_id' => $this->defaultVersion->id,
        'name' => 'Test Ship',
    ]);

    $response = $this->getJson("/api/vehicles/{$vehicle->slug}?include=components");

    $response->assertSuccessful();
});

it('returns 200 when including hardpoints on vehicle index route', function (): void {
    $vehicle = Vehicle::factory()->create();
    VehicleData::factory()->create([
        'vehicle_id' => $vehicle->id,
        'game_version_id' => $this->defaultVersion->id,
        'name' => 'Test Ship',
    ]);

    $response = $this->getJson('/api/vehicles?include=hardpoints');

    $response->assertSuccessful();
});

it('returns 200 when including ports on vehicle index route', function (): void {
    $vehicle = Vehicle::factory()->create();
    VehicleData::factory()->create([
        'vehicle_id' => $vehicle->id,
        'game_version_id' => $this->defaultVersion->id,
        'name' => 'Test Ship',
    ]);

    $response = $this->getJson('/api/vehicles?include=ports');

    $response->assertSuccessful();
});
