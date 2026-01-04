<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\StarCitizen\ShipMatrix\Manufacturer;
use App\Models\StarCitizen\ShipMatrix\ProductionStatus;
use App\Models\StarCitizen\ShipMatrix\Vehicle\Focus;
use App\Models\StarCitizen\ShipMatrix\Vehicle\Size;
use App\Models\StarCitizen\ShipMatrix\Vehicle\Type;
use App\Models\StarCitizen\ShipMatrix\Vehicle\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns ship matrix vehicle filter values with counts', function (): void {
    GameVersion::factory()->create([
        'code' => '3.25.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $manufacturer = Manufacturer::factory()->create(['name' => 'Aegis']);
    $size = Size::factory()->create(['slug' => 'small']);
    $type = Type::factory()->create(['slug' => 'fighter']);
    $status = ProductionStatus::factory()->create(['slug' => 'flight-ready']);
    $focus = Focus::factory()->create(['slug' => 'combat']);

    $vehicle = Vehicle::factory()->create([
        'manufacturer_id' => $manufacturer->id,
        'size_id' => $size->id,
        'type_id' => $type->id,
        'production_status_id' => $status->id,
    ]);
    $vehicle->foci()->attach($focus);

    Vehicle::factory()->create([
        'manufacturer_id' => $manufacturer->id,
        'size_id' => $size->id,
        'type_id' => $type->id,
        'production_status_id' => $status->id,
    ]);

    $response = $this->getJson(route('shipmatrix.vehicles.filters'));

    $response->assertSuccessful();

    $filters = $response->json('filters');

    expect(collect($filters['manufacturer'])->contains(fn (array $row) => $row['value'] === 'Aegis' && $row['count'] === 2))->toBeTrue()
        ->and(collect($filters['size'])->contains(fn (array $row) => $row['value'] === 'small' && $row['count'] === 2))->toBeTrue()
        ->and(collect($filters['type'])->contains(fn (array $row) => $row['value'] === 'fighter' && $row['count'] === 2))->toBeTrue()
        ->and(collect($filters['focus'])->contains(fn (array $row) => $row['value'] === 'combat' && $row['count'] === 1))->toBeTrue()
        ->and(collect($filters['production_status'])->contains(fn (array $row) => $row['value'] === 'flight-ready' && $row['count'] === 2))->toBeTrue()
        ->and(collect($filters['focus'])->contains(fn (array $row) => $row['value'] === null && $row['label'] === 'Unknown'))->toBeTrue();
});
