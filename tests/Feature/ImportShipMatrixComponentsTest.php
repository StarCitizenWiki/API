<?php

declare(strict_types=1);

use App\Jobs\StarCitizen\Vehicle\ImportVehicle;
use App\Models\StarCitizen\ShipMatrix\ProductionNote;
use App\Models\StarCitizen\ShipMatrix\Vehicle\Component;
use App\Models\StarCitizen\ShipMatrix\Vehicle\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;

uses(RefreshDatabase::class);

it('imports vehicle components from ship matrix data', function (): void {
    $payload = json_decode(
        file_get_contents(storage_path('framework/testing/shipmatrix/aurora_es.json')),
        true,
        512,
        JSON_THROW_ON_ERROR
    );

    ProductionNote::query()->create([
        'content_hash' => md5('default'),
    ]);

    (new ImportVehicle(new Collection($payload)))->handle();

    $this->assertDatabaseHas('vehicle_components', [
        'type' => 'radar',
        'name' => 'Radar',
        'component_class' => 'RSIAvionic',
        'component_size' => 'S',
        'manufacturer' => 'TBD',
    ]);

    $component = Component::query()->where([
        'type' => 'radar',
        'name' => 'Radar',
        'component_class' => 'RSIAvionic',
        'component_size' => 'S',
    ])->firstOrFail();

    $vehicle = Vehicle::query()->where('cig_id', (int) $payload['id'])->firstOrFail();

    $this->assertDatabaseHas('vehicle_component', [
        'vehicle_id' => $vehicle->id,
        'component_id' => $component->id,
        'mounts' => 1,
        'size' => 'S',
        'quantity' => 1,
    ]);
});
