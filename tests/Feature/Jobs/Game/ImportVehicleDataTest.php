<?php

declare(strict_types=1);

use App\Jobs\Game\ImportVehicleData;
use App\Models\Game\GameVersion;
use App\Models\Game\Manufacturer;
use App\Models\Game\Vehicle;
use App\Models\Game\VehicleData;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    Storage::fake('scunpacked');

    $this->version = GameVersion::factory()->create(['is_default' => false]);
    $this->manufacturer = Manufacturer::factory()->create([
        'uuid' => fake()->uuid(),
        'name' => 'Test Manufacturer',
        'code' => 'TST',
    ]);
});

function dispatchVehicleImport(int $versionId, array $payload, string $file = 'ships/test.json'): void
{
    Storage::disk('scunpacked')->put($file, json_encode($payload, JSON_THROW_ON_ERROR));

    (new ImportVehicleData($versionId, $file))->handle();
}

it('creates vehicle data on first import', function (): void {
    $payload = vehiclePayload($this->manufacturer->uuid);

    dispatchVehicleImport($this->version->id, $payload);

    $vehicle = Vehicle::query()->where('uuid', $payload['UUID'])->first();
    expect($vehicle)->not->toBeNull();

    $vehicleData = VehicleData::query()
        ->where('vehicle_id', $vehicle->id)
        ->where('game_version_id', $this->version->id)
        ->first();

    expect($vehicleData)->not->toBeNull()
        ->and($vehicleData->name)->toBe('Test Ship')
        ->and($vehicleData->career)->toBe('Combat')
        ->and($vehicleData->is_spaceship)->toBeTrue()
        ->and($vehicleData->data)->toBeArray();
});

it('rewrites the row only when data actually changes', function (): void {
    $payload = vehiclePayload($this->manufacturer->uuid);

    dispatchVehicleImport($this->version->id, $payload);

    // Simulate a genuine data change in the source ship.
    $payload['Name'] = 'Updated Ship Name';
    dispatchVehicleImport($this->version->id, $payload);

    $vehicleData = VehicleData::query()
        ->where('game_version_id', $this->version->id)
        ->first();

    // The guard must let a genuine change through and persist it.
    expect($vehicleData->name)->toBe('Updated Ship Name');
});

/**
 * Build a minimal, valid vehicle payload.
 */
function vehiclePayload(string $manufacturerUuid): array
{
    return [
        'UUID' => '22222222-2222-2222-2222-222222222222',
        'ClassName' => 'TEST_SHIP',
        'Name' => 'Test Ship',
        'Career' => 'Combat',
        'Role' => 'Fighter',
        'IsVehicle' => false,
        'IsGravlev' => false,
        'IsSpaceship' => true,
        'Size' => 2,
        'Manufacturer' => [
            'UUID' => $manufacturerUuid,
            'Name' => 'Test Manufacturer',
        ],
    ];
}
