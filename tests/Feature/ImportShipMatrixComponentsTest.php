<?php

declare(strict_types=1);

use App\Jobs\StarCitizen\Vehicle\ImportVehicle;
use App\Models\StarCitizen\ShipMatrix\ProductionNote;
use App\Models\StarCitizen\ShipMatrix\Vehicle\Component;
use App\Models\StarCitizen\ShipMatrix\Vehicle\Vehicle;
use Illuminate\Support\Collection;

it('coerces fractional numeric fields to fit integer columns', function (): void {
    $payload = 'framework/testing/shipmatrix/aurora_es.json'
            |> storage_path(...)
            |> file_get_contents(...)
            |> (static fn ($x) => json_decode($x, true, 512, JSON_THROW_ON_ERROR));

    // RSI emits fractional values for fields bound to integer columns (e.g. mass "2339240.06"). Postgres rejects those for bigint, so the import must coerce.
    $payload['id'] = '99999';
    $payload['mass'] = '2339240.06';
    $payload['min_crew'] = '1.0';
    $payload['max_crew'] = '2.9';
    $payload['scm_speed'] = '190.7';
    $payload['afterburner_speed'] = '1140.5';

    ProductionNote::query()->create([
        'translation' => ['en' => 'None'],
    ]);

    new ImportVehicle(new Collection($payload))->handle();

    $vehicle = Vehicle::query()->where('cig_id', (int) $payload['id'])->firstOrFail();

    expect($vehicle->mass)->toBe(2339240)
        ->and($vehicle->min_crew)->toBe(1)
        ->and($vehicle->max_crew)->toBe(2)
        ->and($vehicle->scm_speed)->toBe(190)
        ->and($vehicle->afterburner_speed)->toBe(1140);
});

it('imports vehicle components from ship matrix data', function (): void {
    $payload = 'framework/testing/shipmatrix/aurora_es.json'
            |> storage_path(...)
            |> file_get_contents(...)
            |> (fn ($x) => json_decode($x, true, 512, JSON_THROW_ON_ERROR));

    ProductionNote::query()->create([
        'translation' => ['en' => 'None'],
    ]);

    (new ImportVehicle(new Collection($payload)))->handle();

    $this->assertDatabaseHas('shipmatrix_vehicle_components', [
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

    $this->assertDatabaseHas('shipmatrix_vehicle_component', [
        'vehicle_id' => $vehicle->id,
        'component_id' => $component->id,
        'mounts' => 1,
        'size' => 'S',
        'quantity' => 1,
    ]);
});
