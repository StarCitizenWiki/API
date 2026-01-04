<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\Game\Manufacturer;
use App\Models\Game\Vehicle;
use App\Models\Game\VehicleData;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns vehicle filter values with counts', function (): void {
    $version = GameVersion::factory()->create([
        'code' => '3.25.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $manufacturer = Manufacturer::factory()->create([
        'name' => 'Origin',
        'code' => 'ORIG',
    ]);

    $vehicle = Vehicle::factory()->create();
    VehicleData::factory()
        ->for($vehicle)
        ->for($version, 'gameVersion')
        ->for($manufacturer)
        ->create([
            'career' => 'Transport',
            'role' => 'Cargo',
            'is_vehicle' => true,
            'is_gravlev' => false,
            'is_spaceship' => true,
            'size' => 3,
            'data' => [],
        ]);

    $unknown = Vehicle::factory()->create();
    VehicleData::factory()
        ->for($unknown)
        ->for($version, 'gameVersion')
        ->create([
            'career' => null,
            'role' => null,
            'manufacturer_id' => null,
            'is_vehicle' => false,
            'is_gravlev' => true,
            'is_spaceship' => false,
            'size' => null,
            'data' => [],
        ]);

    $response = $this->getJson('/api/vehicles/filters');

    $response->assertSuccessful();

    $filters = $response->json('filters');

    expect(collect($filters['manufacturer'])->contains(fn (array $row) => $row['value'] === 'Origin' && $row['count'] === 1))->toBeTrue()
        ->and(collect($filters['manufacturer'])->contains(fn (array $row) => $row['value'] === null && $row['label'] === 'Unknown'))->toBeTrue()
        ->and(collect($filters['career'])->contains(fn (array $row) => $row['value'] === 'Transport' && $row['count'] === 1))->toBeTrue()
        ->and(collect($filters['role'])->contains(fn (array $row) => $row['value'] === 'Cargo' && $row['count'] === 1))->toBeTrue()
        ->and(collect($filters['size'])->contains(fn (array $row) => $row['value'] === 3 && $row['count'] === 1))->toBeTrue()
        ->and(collect($filters['is_vehicle'])->contains(fn (array $row) => $row['value'] === true && $row['count'] === 1))->toBeTrue()
        ->and(collect($filters['is_gravlev'])->contains(fn (array $row) => $row['value'] === true && $row['count'] === 1))->toBeTrue()
        ->and(collect($filters['is_spaceship'])->contains(fn (array $row) => $row['value'] === true && $row['count'] === 1))->toBeTrue();
});
