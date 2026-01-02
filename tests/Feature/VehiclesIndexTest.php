<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\Game\Manufacturer;
use App\Models\Game\Vehicle;
use App\Models\Game\VehicleData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

it('returns the vehicle list without error', function (): void {
    $version = GameVersion::query()->create([
        'code' => 'test-version',
        'channel' => 'testing',
        'is_default' => true,
    ]);

    $manufacturer = Manufacturer::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'Test Manufacturer',
        'code' => 'TEST',
    ]);

    $vehicle = Vehicle::query()->create([
        'uuid' => (string) Str::uuid(),
    ]);

    VehicleData::query()->create([
        'vehicle_id' => $vehicle->id,
        'game_version_id' => $version->id,
        'manufacturer_id' => $manufacturer->id,
        'class_name' => 'TestVehicle_Class',
        'name' => 'Test Vehicle',
        'data' => [],
    ]);

    $response = $this->getJson(route('vehicles.index'));

    $response->assertOk();
    expect($response->json('data.0.uuid'))->toBe($vehicle->uuid);
});
