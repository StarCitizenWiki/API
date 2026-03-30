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

    $this->getJson(route('shipmatrix.vehicles.filters'))
        ->assertOk()
        ->assertExactJson([
            'filters' => [
                'manufacturer' => [
                    ['value' => 'Aegis', 'label' => 'Aegis', 'count' => 2],
                ],
                'size' => [
                    ['value' => 'small', 'label' => 'small', 'count' => 2],
                ],
                'type' => [
                    ['value' => 'fighter', 'label' => 'fighter', 'count' => 2],
                ],
                'focus' => [
                    ['value' => 'combat', 'label' => 'combat', 'count' => 1],
                    ['value' => null, 'label' => 'Unknown', 'count' => 1],
                ],
                'production_status' => [
                    ['value' => 'flight-ready', 'label' => 'flight-ready', 'count' => 2],
                ],
            ],
        ]);
});
