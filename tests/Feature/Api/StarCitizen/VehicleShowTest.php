<?php

declare(strict_types=1);

use App\Models\StarCitizen\ShipMatrix\Manufacturer;
use App\Models\StarCitizen\ShipMatrix\ProductionNote;
use App\Models\StarCitizen\ShipMatrix\ProductionStatus;
use App\Models\StarCitizen\ShipMatrix\Vehicle\Size;
use App\Models\StarCitizen\ShipMatrix\Vehicle\Type;
use App\Models\StarCitizen\ShipMatrix\Vehicle\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns a vehicle by slug', function (): void {
    $manufacturer = Manufacturer::query()->create([
        'cig_id' => 1,
        'name' => 'Aegis Dynamics',
        'name_short' => 'AEGS',
    ]);

    $size = Size::query()->create(['slug' => 'small']);
    $type = Type::query()->create(['slug' => 'fighter']);
    $note = ProductionNote::query()->create([
        'content_hash' => 'test-hash',
    ]);

    $status = ProductionStatus::query()->create(['slug' => 'flight-ready']);

    $vehicle = Vehicle::query()->create([
        'cig_id' => 1,
        'name' => 'Avenger',
        'slug' => 'avenger',
        'manufacturer_id' => $manufacturer->id,
        'size_id' => $size->id,
        'type_id' => $type->id,
        'production_status_id' => $status->id,
        'production_note_id' => $note->id,
        'chassis_id' => 1,
    ]);

    $response = $this->getJson(route('shipmatrix.vehicles.show', ['vehicle' => 'avenger']));

    $response->assertOk();
    expect($response->json('data.id'))->toBe($vehicle->cig_id);
    expect($response->json('data.name'))->toBe('Avenger');
    expect($response->json('data.slug'))->toBe('avenger');
});

it('returns 404 for non-existent vehicle', function (): void {
    $response = $this->getJson(route('shipmatrix.vehicles.show', ['vehicle' => 'non-existent']));

    $response->assertNotFound();
});

it('returns vehicle with correct structure', function (): void {
    $manufacturer = Manufacturer::query()->create([
        'cig_id' => 1,
        'name' => 'Aegis Dynamics',
        'name_short' => 'AEGS',
    ]);

    $size = Size::query()->create(['slug' => 'small']);
    $type = Type::query()->create(['slug' => 'fighter']);
    $note = ProductionNote::query()->create([
        'content_hash' => 'test-hash',
    ]);

    $status = ProductionStatus::query()->create(['slug' => 'flight-ready']);

    Vehicle::query()->create([
        'cig_id' => 1,
        'name' => 'Avenger',
        'slug' => 'avenger',
        'manufacturer_id' => $manufacturer->id,
        'size_id' => $size->id,
        'type_id' => $type->id,
        'production_status_id' => $status->id,
        'production_note_id' => $note->id,
        'chassis_id' => 1,
        'length' => 22.5,
        'beam' => 16.5,
        'height' => 7.0,
        'mass' => 52000,
        'cargo_capacity' => 8,
        'min_crew' => 1,
        'max_crew' => 1,
        'scm_speed' => 220,
        'afterburner_speed' => 1150,
    ]);

    $response = $this->getJson(route('shipmatrix.vehicles.show', ['vehicle' => 'avenger']));

    $response->assertOk();
    $response->assertJsonStructure([
        'data' => [
            'id',
            'chassis_id',
            'name',
            'slug',
            'sizes' => [
                'length',
                'beam',
                'height',
            ],
            'mass',
            'cargo_capacity',
            'crew' => [
                'min',
                'max',
            ],
            'speed' => [
                'scm',
                'max',
            ],
            'agility' => [
                'pitch',
                'yaw',
                'roll',
                'acceleration' => [
                    'x_axis',
                    'y_axis',
                    'z_axis',
                ],
            ],
            'foci',
            'production_status',
            'production_note',
            'type',
            'description',
            'size',
            'msrp',
            'skus',
            'manufacturer' => [
                'code',
                'name',
            ],
            'loaner',
            'updated_at',
        ],
    ]);
});

it('handles url-encoded slugs', function (): void {
    $manufacturer = Manufacturer::query()->create([
        'cig_id' => 1,
        'name' => 'Anvil Aerospace',
        'name_short' => 'ANVL',
    ]);

    $size = Size::query()->create(['slug' => 'small']);
    $type = Type::query()->create(['slug' => 'fighter']);
    $note = ProductionNote::query()->create([
        'content_hash' => 'test-hash',
    ]);

    $status = ProductionStatus::query()->create(['slug' => 'flight-ready']);

    Vehicle::query()->create([
        'cig_id' => 1,
        'name' => 'F7C Hornet',
        'slug' => 'f7c-hornet',
        'manufacturer_id' => $manufacturer->id,
        'size_id' => $size->id,
        'type_id' => $type->id,
        'production_status_id' => $status->id,
        'production_note_id' => $note->id,
        'chassis_id' => 1,
    ]);

    $response = $this->getJson(route('shipmatrix.vehicles.show', ['vehicle' => 'f7c-hornet']));

    $response->assertOk();
    expect($response->json('data.name'))->toBe('F7C Hornet');
});
