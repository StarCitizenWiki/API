<?php

declare(strict_types=1);

use App\Models\StarCitizen\ShipMatrix\Manufacturer;
use App\Models\StarCitizen\ShipMatrix\ProductionStatus;
use App\Models\StarCitizen\ShipMatrix\Vehicle\Focus;
use App\Models\StarCitizen\ShipMatrix\Vehicle\Size;
use App\Models\StarCitizen\ShipMatrix\Vehicle\Type;
use App\Models\StarCitizen\ShipMatrix\Vehicle\Vehicle;

it('returns ship matrix vehicle filter values with counts', function (): void {
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

    assertFacetsEqual(
        $this->getJson(route('shipmatrix.vehicles.filters'))->assertOk(),
        [
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
    );
});

it('narrows facets when a filter is supplied', function (): void {
    $aegis = Manufacturer::factory()->create(['name' => 'Aegis']);
    $anvil = Manufacturer::factory()->create(['name' => 'Anvil']);
    $small = Size::factory()->create(['slug' => 'small']);
    $medium = Size::factory()->create(['slug' => 'medium']);
    $fighter = Type::factory()->create(['slug' => 'fighter']);
    $freighter = Type::factory()->create(['slug' => 'freighter']);
    $flightReady = ProductionStatus::factory()->create(['slug' => 'flight-ready']);
    $concept = ProductionStatus::factory()->create(['slug' => 'concept']);
    $combat = Focus::factory()->create(['slug' => 'combat']);
    $transport = Focus::factory()->create(['slug' => 'transport']);

    $matching = Vehicle::factory()->create([
        'manufacturer_id' => $aegis->id,
        'size_id' => $small->id,
        'type_id' => $fighter->id,
        'production_status_id' => $flightReady->id,
    ]);
    $matching->foci()->attach($combat);

    $nonMatching = Vehicle::factory()->create([
        'manufacturer_id' => $anvil->id,
        'size_id' => $medium->id,
        'type_id' => $freighter->id,
        'production_status_id' => $concept->id,
    ]);
    $nonMatching->foci()->attach($transport);

    assertFacetsEqual(
        $this->getJson(route('shipmatrix.vehicles.filters', [
            'filter' => ['manufacturer' => 'Aegis'],
        ]))->assertOk(),
        [
            'manufacturer' => [
                ['value' => 'Aegis', 'label' => 'Aegis', 'count' => 1],
            ],
            'size' => [
                ['value' => 'small', 'label' => 'small', 'count' => 1],
            ],
            'type' => [
                ['value' => 'fighter', 'label' => 'fighter', 'count' => 1],
            ],
            'focus' => [
                ['value' => 'combat', 'label' => 'combat', 'count' => 1],
            ],
            'production_status' => [
                ['value' => 'flight-ready', 'label' => 'flight-ready', 'count' => 1],
            ],
        ],
    );
});
