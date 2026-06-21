<?php

declare(strict_types=1);

use App\Models\StarCitizen\ShipMatrix\Manufacturer;
use App\Models\StarCitizen\ShipMatrix\ProductionNote;
use App\Models\StarCitizen\ShipMatrix\ProductionStatus;
use App\Models\StarCitizen\ShipMatrix\Vehicle\Focus;
use App\Models\StarCitizen\ShipMatrix\Vehicle\Size;
use App\Models\StarCitizen\ShipMatrix\Vehicle\Type;
use App\Models\StarCitizen\ShipMatrix\Vehicle\Vehicle;

beforeEach(function (): void {
    $this->manufacturer = Manufacturer::query()->create([
        'cig_id' => 1,
        'name' => 'Aegis Dynamics',
        'name_short' => 'AEGS',
    ]);

    $this->size = Size::query()->create([
        'slug' => 'small',
        'translation' => ['en' => 'Small'],
    ]);

    $this->type = Type::query()->create([
        'slug' => 'fighter',
        'translation' => ['en' => 'Combat'],
    ]);

    $this->note = ProductionNote::query()->create([
        'translation' => ['en' => 'Test note'],
    ]);

    $this->status = ProductionStatus::query()->create([
        'slug' => 'flight-ready',
        'translation' => ['en' => 'Flight Ready'],
    ]);
});

it('returns a vehicle by slug', function (): void {
    $vehicle = Vehicle::query()->create([
        'cig_id' => 1,
        'name' => 'Avenger',
        'slug' => 'avenger',
        'manufacturer_id' => $this->manufacturer->id,
        'size_id' => $this->size->id,
        'type_id' => $this->type->id,
        'production_status_id' => $this->status->id,
        'production_note_id' => $this->note->id,
        'chassis_id' => 1,
    ]);

    $response = $this->getJson(route('shipmatrix.vehicles.show', ['vehicle' => 'avenger']));

    $response->assertOk();
    expect($response->json('data.id'))->toBe($vehicle->cig_id)
        ->and($response->json('data.name'))->toBe('Avenger')
        ->and($response->json('data.slug'))->toBe('avenger');
});

it('returns 404 for non-existent vehicle', function (): void {
    $response = $this->getJson(route('shipmatrix.vehicles.show', ['vehicle' => 'non-existent']));

    $response->assertNotFound();
});

it('returns vehicle with correct structure', function (): void {
    $vehicle = Vehicle::query()->create([
        'cig_id' => 1,
        'name' => 'Avenger',
        'slug' => 'avenger',
        'manufacturer_id' => $this->manufacturer->id,
        'size_id' => $this->size->id,
        'type_id' => $this->type->id,
        'production_status_id' => $this->status->id,
        'production_note_id' => $this->note->id,
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

    $vehicle->foci()->attach(Focus::query()->create([
        'slug' => 'combat',
        'translation' => ['en' => 'Combat'],
    ]));

    $response = $this->getJson(route('shipmatrix.vehicles.show', ['vehicle' => 'avenger']));

    $response->assertOk();
    $response->assertJsonCount(1, 'data.foci')
        ->assertJsonCount(0, 'data.skus')
        ->assertJsonPath('data.id', $vehicle->cig_id)
        ->assertJsonPath('data.name', 'Avenger')
        ->assertJsonPath('data.slug', 'avenger')
        ->assertJsonPath('data.sizes.length', 22.5)
        ->assertJsonPath('data.dimension.length', 22.5)
        ->assertJsonPath('data.dimension.width', 16.5)
        ->assertJsonPath('data.dimension.height', 7)
        ->assertJsonPath('data.crew.min', 1)
        ->assertJsonPath('data.crew.max', 1)
        ->assertJsonPath('data.speed.scm', 220)
        ->assertJsonPath('data.speed.max', 1150)
        ->assertJsonPath('data.foci.0.en', 'Combat')
        ->assertJsonPath('data.production_status.en', 'Flight Ready')
        ->assertJsonPath('data.production_note.en', 'Test note')
        ->assertJsonPath('data.type.en', 'Combat')
        ->assertJsonPath('data.size.en', 'Small')
        ->assertJsonPath('data.manufacturer.code', 'AEGS')
        ->assertJsonPath('data.manufacturer.name', 'Aegis Dynamics')
        ->assertJsonPath('data.link', route('shipmatrix.vehicles.show', 'avenger'))
        ->assertJsonPath('data.updated_at', $vehicle->fresh()->updated_at?->toJSON())
        ->assertJsonPath('meta.valid_relations', ['components', 'loaner', 'skus'])
        ->assertJsonPath('meta.deprecated_fields.sizes', 'Use length, width, and height properties from dimension instead');
});

it('handles url-encoded slugs', function (): void {
    $vehicleSlug = 'f7c hornet';

    Vehicle::query()->create([
        'cig_id' => 1,
        'name' => 'F7C Hornet',
        'slug' => $vehicleSlug,
        'manufacturer_id' => $this->manufacturer->id,
        'size_id' => $this->size->id,
        'type_id' => $this->type->id,
        'production_status_id' => $this->status->id,
        'production_note_id' => $this->note->id,
        'chassis_id' => 1,
    ]);

    $encodedRoute = route('shipmatrix.vehicles.show', ['vehicle' => $vehicleSlug]);

    expect($encodedRoute)->toContain('%20');

    $response = $this->getJson($encodedRoute);

    $response->assertOk();
    expect($response->json('data.name'))->toBe('F7C Hornet');
});
