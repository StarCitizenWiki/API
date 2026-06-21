<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\Game\Vehicle;
use App\Models\Game\VehicleData;

beforeEach(function (): void {
    $this->defaultVersion = GameVersion::factory()->create(['is_default' => true]);
});

function bindVehicle(GameVersion $version, string $name = 'Test Ship', array $overrides = []): Vehicle
{
    $vehicle = Vehicle::factory()->create();

    VehicleData::factory()->create(array_merge([
        'vehicle_id' => $vehicle->id,
        'game_version_id' => $version->id,
        'name' => $name,
        'display_name' => $name,
    ], $overrides));

    return $vehicle;
}

// CustomEagerLoadInclude accepts these include names but invokes $query->with([]):
// the query params are a documented no-op and must not 500. The real components
// assertion lives in VehicleShipMatrixIntegrationTest.
it('accepts documented no-op includes on vehicle show route without error', function (string $include): void {
    $vehicle = bindVehicle($this->defaultVersion);

    $this->getJson("/api/vehicles/{$vehicle->slug}?include={$include}")
        ->assertSuccessful()
        ->assertJsonPath('data.name', 'Test Ship')
        ->assertJsonStructure(['data' => ['ports']]);
})->with(['hardpoints', 'ports', 'components']);

it('treats percent characters as literal text for exact vehicle lookups', function (): void {
    bindVehicle($this->defaultVersion, 'WildcardXShip', ['class_name' => 'WildcardXShip']);
    $exact = bindVehicle($this->defaultVersion, 'Wildcard%Ship', ['class_name' => 'Wildcard_Percent_Ship']);

    $this->getJson('/api/vehicles/'.rawurlencode('Wildcard%Ship'))
        ->assertSuccessful()
        ->assertJsonPath('data.uuid', $exact->uuid)
        ->assertJsonPath('data.name', 'Wildcard%Ship');
});

it('ignores medical beds without a tier when resolving max medical tier', function (): void {
    $vehicle = bindVehicle($this->defaultVersion, 'Medical Ship', [
        'max_medical_tier' => 'T2',
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

    $this->getJson("/api/vehicles/{$vehicle->uuid}")
        ->assertSuccessful()
        ->assertJsonPath('data.max_medical_tier', 'T2')
        ->assertJsonPath('data.seating.medical_beds.T2', 1);
});

it('accepts hardpoints include on vehicle index route', function (): void {
    bindVehicle($this->defaultVersion);

    $response = $this->getJson('/api/vehicles?include=hardpoints');

    $response->assertSuccessful();
    expect($response->json('data'))->toHaveCount(1)
        ->and($response->json('data.0.name'))->toBe('Test Ship');
});

it('accepts ports include on vehicle index route', function (): void {
    bindVehicle($this->defaultVersion);

    $response = $this->getJson('/api/vehicles?include=ports');

    $response->assertSuccessful();
    expect($response->json('data'))->toHaveCount(1)
        ->and($response->json('data.0.name'))->toBe('Test Ship');
});

it('returns filtered index ports as a JSON list', function (): void {
    bindVehicle($this->defaultVersion, 'Filtered Ports Ship', [
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
