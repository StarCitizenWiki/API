<?php

declare(strict_types=1);

use App\Models\StarCitizen\ShipMatrix\Manufacturer;
use App\Models\StarCitizen\ShipMatrix\ProductionNote;
use App\Models\StarCitizen\ShipMatrix\ProductionStatus;
use App\Models\StarCitizen\ShipMatrix\Vehicle\Size;
use App\Models\StarCitizen\ShipMatrix\Vehicle\Type;
use App\Models\StarCitizen\ShipMatrix\Vehicle\Vehicle;

beforeEach(function (): void {
    $this->manufacturer = Manufacturer::query()->create([
        'cig_id' => 1,
        'name' => 'Aegis Dynamics',
        'name_short' => 'AEGS',
    ]);

    $this->size = Size::query()->create(['slug' => 'small']);

    $this->type = Type::query()->create(['slug' => 'fighter']);

    $this->note = ProductionNote::query()->create([
        'translation' => ['en' => 'Test note'],
    ]);

    $this->status = ProductionStatus::query()->create(['slug' => 'flight-ready']);
});

it('searches vehicles by name', function (): void {
    $vehicles = [
        ['name' => 'Avenger Titan', 'slug' => 'avenger-titan'],
        ['name' => 'Avenger Stalker', 'slug' => 'avenger-stalker'],
        ['name' => 'Gladius', 'slug' => 'gladius'],
    ];

    foreach ($vehicles as $i => $vehicle) {
        Vehicle::query()->create([
            'cig_id' => $i + 1,
            'name' => $vehicle['name'],
            'slug' => $vehicle['slug'],
            'manufacturer_id' => $this->manufacturer->id,
            'size_id' => $this->size->id,
            'type_id' => $this->type->id,
            'production_status_id' => $this->status->id,
            'production_note_id' => $this->note->id,
            'chassis_id' => 1,
        ]);
    }

    $response = $this->postJson(route('shipmatrix.vehicles.search'), [
        'query' => 'Avenger',
    ]);

    $response->assertOk();
    expect($response->json('data'))->toHaveCount(2);
    expect($response->json('data.0.name'))->toContain('Avenger');
    expect($response->json('data.1.name'))->toContain('Avenger');
});

it('returns empty results when no results found', function (): void {
    Vehicle::query()->create([
        'cig_id' => 1,
        'name' => 'Gladius',
        'slug' => 'gladius',
        'manufacturer_id' => $this->manufacturer->id,
        'size_id' => $this->size->id,
        'type_id' => $this->type->id,
        'production_status_id' => $this->status->id,
        'production_note_id' => $this->note->id,
        'chassis_id' => 1,
    ]);

    $response = $this->postJson(route('shipmatrix.vehicles.search'), [
        'query' => 'NonExistentVehicle',
    ]);

    $response->assertOk();
    expect($response->json('data'))->toBeEmpty();
    expect($response->json('meta.total'))->toBe(0);
});

it('validates query parameter is required', function (): void {
    $response = $this->postJson(route('shipmatrix.vehicles.search'), []);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['query']);
});

it('validates query parameter has minimum length', function (): void {
    $response = $this->postJson(route('shipmatrix.vehicles.search'), [
        'query' => '',
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['query']);
});

it('validates query parameter has maximum length', function (): void {
    $response = $this->postJson(route('shipmatrix.vehicles.search'), [
        'query' => str_repeat('a', 256),
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['query']);
});

it('supports pagination in search results', function (): void {
    for ($i = 1; $i <= 20; $i++) {
        Vehicle::query()->create([
            'cig_id' => $i,
            'name' => "Test Vehicle {$i}",
            'slug' => "test-vehicle-{$i}",
            'manufacturer_id' => $this->manufacturer->id,
            'size_id' => $this->size->id,
            'type_id' => $this->type->id,
            'production_status_id' => $this->status->id,
            'production_note_id' => $this->note->id,
            'chassis_id' => 1,
        ]);
    }

    $response = $this->postJson(route('shipmatrix.vehicles.search', [
        'sort' => 'id',
        'page' => [
            'number' => 2,
            'size' => 5,
        ],
    ]), [
        'query' => 'Test',
    ]);

    $response->assertOk()
        ->assertJsonCount(5, 'data')
        ->assertJsonPath('meta.current_page', 2)
        ->assertJsonPath('meta.per_page', 5)
        ->assertJsonPath('meta.total', 20)
        ->assertJsonPath('meta.last_page', 4);

    expect(collect($response->json('data'))->pluck('id')->all())->toBe([6, 7, 8, 9, 10]);
});

it('supports filters with search', function (): void {
    $drake = Manufacturer::query()->create([
        'cig_id' => 2,
        'name' => 'Drake Interplanetary',
        'name_short' => 'DRAK',
    ]);

    Vehicle::query()->create([
        'cig_id' => 1,
        'name' => 'Combat Fighter Alpha',
        'slug' => 'combat-fighter-alpha',
        'manufacturer_id' => $this->manufacturer->id,
        'size_id' => $this->size->id,
        'type_id' => $this->type->id,
        'production_status_id' => $this->status->id,
        'production_note_id' => $this->note->id,
        'chassis_id' => 1,
    ]);

    Vehicle::query()->create([
        'cig_id' => 2,
        'name' => 'Combat Fighter Beta',
        'slug' => 'combat-fighter-beta',
        'manufacturer_id' => $drake->id,
        'size_id' => $this->size->id,
        'type_id' => $this->type->id,
        'production_status_id' => $this->status->id,
        'production_note_id' => $this->note->id,
        'chassis_id' => 1,
    ]);

    $response = $this->postJson(route('shipmatrix.vehicles.search', ['filter' => ['manufacturer' => 'Aegis Dynamics']]), [
        'query' => 'Combat',
    ]);

    $response->assertOk();
    expect($response->json('data'))->toHaveCount(1);
    expect($response->json('data.0.name'))->toBe('Combat Fighter Alpha');
});
