<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\Game\Manufacturer;
use App\Models\Game\Vehicle;
use App\Models\Game\VehicleData;

beforeEach(function (): void {
    $this->version = GameVersion::factory()->create([
        'code' => '4.0.0-LIVE.1',
        'channel' => 'testing',
        'is_default' => true,
    ]);

    $this->manufacturer = Manufacturer::factory()->create([
        'name' => 'Test Manufacturer',
        'code' => 'TEST',
    ]);

    $this->alpha = VehicleData::factory()
        ->for(Vehicle::factory(), 'vehicle')
        ->for($this->version, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'name' => 'Alpha Ship',
            'class_name' => 'TEST_Alpha',
            'data' => [
                'Parts' => [
                    ['Name' => 'Body', 'DamageMax' => 1500],
                ],
                'DamageBeforeDestruction' => [
                    ['Name' => 'Body', 'DestructionDamage' => 1000],
                ],
            ],
        ]);

    $this->zeta = VehicleData::factory()
        ->for(Vehicle::factory(), 'vehicle')
        ->for($this->version, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'name' => 'Zeta Ship',
            'class_name' => 'TEST_Zeta',
            'data' => [
                'Parts' => [
                    ['Name' => 'Body', 'DamageMax' => 2500],
                ],
                'DamageBeforeDestruction' => [
                    ['Name' => 'Body', 'DestructionDamage' => 2000],
                ],
            ],
        ]);
});

it('resolves each vehicle body destruction damage on the index', function (): void {
    $response = $this->getJson('/api/vehicles');

    $response->assertOk();

    $partsByVehicle = collect($response->json('data'))
        ->mapWithKeys(fn (array $vehicle): array => [$vehicle['uuid'] => $vehicle['parts'][0]]);

    expect($partsByVehicle[$this->alpha->vehicle->uuid]['destruction_damage'])->toBe(1000)
        ->and($partsByVehicle[$this->zeta->vehicle->uuid]['destruction_damage'])->toBe(2000);
});

it('reports matching destruction damage on index and show', function (): void {
    $indexed = collect($this->getJson('/api/vehicles')->json('data'))
        ->mapWithKeys(fn (array $vehicle): array => [$vehicle['uuid'] => $vehicle['parts'][0]['destruction_damage']]);

    foreach ([$this->alpha, $this->zeta] as $vehicleData) {
        $shown = $this->getJson("/api/vehicles/{$vehicleData->vehicle->uuid}")
            ->assertOk()
            ->json('data.parts.0.destruction_damage');

        expect($shown)->toBe($indexed[$vehicleData->vehicle->uuid]);
    }
});
