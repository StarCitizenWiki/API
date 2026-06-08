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

it('treats percent characters as literal text for exact vehicle lookups', function (): void {
    $decoy = Vehicle::factory()->create();
    VehicleData::factory()->create([
        'vehicle_id' => $decoy->id,
        'game_version_id' => $this->defaultVersion->id,
        'name' => 'WildcardXShip',
        'display_name' => 'WildcardXShip',
        'class_name' => 'WildcardXShip',
    ]);

    $exact = Vehicle::factory()->create();
    VehicleData::factory()->create([
        'vehicle_id' => $exact->id,
        'game_version_id' => $this->defaultVersion->id,
        'name' => 'Wildcard%Ship',
        'display_name' => 'Wildcard%Ship',
        'class_name' => 'Wildcard_Percent_Ship',
    ]);

    $response = $this->getJson('/api/vehicles/'.rawurlencode('Wildcard%Ship'));

    $response->assertSuccessful()
        ->assertJsonPath('data.uuid', $exact->uuid)
        ->assertJsonPath('data.name', 'Wildcard%Ship');
});

it('ignores medical beds without a tier when resolving max medical tier', function (): void {
    $vehicle = Vehicle::factory()->create();
    VehicleData::factory()->create([
        'vehicle_id' => $vehicle->id,
        'game_version_id' => $this->defaultVersion->id,
        'name' => 'Medical Ship',
        'display_name' => 'Medical Ship',
        'data' => [
            'Seating' => [
                'MedicalBeds' => [
                    ['Count' => 1],
                    ['Tier' => 'T2', 'Count' => 1],
                    ['Tier' => 'T1', 'Count' => 1],
                ],
            ],
        ],
    ]);

    $response = $this->getJson("/api/vehicles/{$vehicle->uuid}");

    $response->assertSuccessful()
        ->assertJsonPath('data.max_medical_tier', 'T2')
        ->assertJsonPath('data.seating.medical_beds.T2', 1);
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

it('returns filtered index ports as a JSON list', function (): void {
    $vehicle = Vehicle::factory()->create();
    VehicleData::factory()->create([
        'vehicle_id' => $vehicle->id,
        'game_version_id' => $this->defaultVersion->id,
        'name' => 'Filtered Ports Ship',
        'display_name' => 'Filtered Ports Ship',
        'data' => [
            'Loadout' => [
                [
                    'HardpointName' => 'hardpoint_docking_empty',
                    'Type' => 'DockingCollar.UNDEFINED',
                    'UUID' => fake()->uuid(),
                ],
                [
                    'HardpointName' => 'hardpoint_weapon_nose',
                    'Type' => 'WeaponGun.Gun',
                    'UUID' => fake()->uuid(),
                ],
            ],
        ],
    ]);

    $response = $this->getJson('/api/vehicles?include=ports');

    $response->assertSuccessful()
        ->assertJsonPath('data.0.ports.0.category_label', 'Weapons');

    expect(array_is_list($response->json('data.0.ports')))->toBeTrue();
});
