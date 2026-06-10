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

it('computes max_medical_tier from medical beds', function (): void {
    $vehicleUuid = fake()->uuid();
    $payload = baseVehiclePayload($vehicleUuid, $this->manufacturer->uuid, [
        'Seating' => [
            'MedicalBeds' => [
                ['Tier' => 'T1', 'Count' => 1],
                ['Tier' => 'T3', 'Count' => 1],
            ],
        ],
    ]);

    Storage::disk('scunpacked')->put('ships/test.json', json_encode($payload));

    (new ImportVehicleData($this->version->id, 'ships/test.json'))->handle();

    $data = VehicleData::first();
    expect($data->max_medical_tier)->toBe('T3');
});

it('returns null max_medical_tier when no medical beds exist', function (): void {
    $vehicleUuid = fake()->uuid();
    $payload = baseVehiclePayload($vehicleUuid, $this->manufacturer->uuid);

    Storage::disk('scunpacked')->put('ships/test.json', json_encode($payload));

    (new ImportVehicleData($this->version->id, 'ships/test.json'))->handle();

    $data = VehicleData::first();
    expect($data->max_medical_tier)->toBeNull();
});

it('returns null max_medical_tier when medical beds array is empty', function (): void {
    $vehicleUuid = fake()->uuid();
    $payload = baseVehiclePayload($vehicleUuid, $this->manufacturer->uuid, [
        'Seating' => [
            'MedicalBeds' => [],
        ],
    ]);

    Storage::disk('scunpacked')->put('ships/test.json', json_encode($payload));

    (new ImportVehicleData($this->version->id, 'ships/test.json'))->handle();

    $data = VehicleData::first();
    expect($data->max_medical_tier)->toBeNull();
});

it('ignores medical beds without a tier', function (): void {
    $vehicleUuid = fake()->uuid();
    $payload = baseVehiclePayload($vehicleUuid, $this->manufacturer->uuid, [
        'Seating' => [
            'MedicalBeds' => [
                ['Count' => 1],
                ['Tier' => 'T2', 'Count' => 1],
            ],
        ],
    ]);

    Storage::disk('scunpacked')->put('ships/test.json', json_encode($payload));

    (new ImportVehicleData($this->version->id, 'ships/test.json'))->handle();

    $data = VehicleData::first();
    expect($data->max_medical_tier)->toBe('T2');
});

it('selects highest tier from multiple beds', function (): void {
    $vehicleUuid = fake()->uuid();
    $payload = baseVehiclePayload($vehicleUuid, $this->manufacturer->uuid, [
        'Seating' => [
            'MedicalBeds' => [
                ['Tier' => 'T1', 'Count' => 2],
                ['Tier' => 'T2', 'Count' => 1],
                ['Tier' => 'T1', 'Count' => 1],
            ],
        ],
    ]);

    Storage::disk('scunpacked')->put('ships/test.json', json_encode($payload));

    (new ImportVehicleData($this->version->id, 'ships/test.json'))->handle();

    $data = VehicleData::first();
    expect($data->max_medical_tier)->toBe('T2');
});

/**
 * Build a minimal vehicle payload with optional extra data merged in.
 */
function baseVehiclePayload(string $uuid, string $manufacturerUuid, array $extra = []): array
{
    return array_merge([
        'UUID' => $uuid,
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
    ], $extra);
}
