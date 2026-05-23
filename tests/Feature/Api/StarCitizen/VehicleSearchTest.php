<?php

declare(strict_types=1);

use App\Models\StarCitizen\ShipMatrix\Manufacturer;
use App\Models\StarCitizen\ShipMatrix\ProductionNote;
use App\Models\StarCitizen\ShipMatrix\ProductionStatus;
use App\Models\StarCitizen\ShipMatrix\Vehicle\Size;
use App\Models\StarCitizen\ShipMatrix\Vehicle\Type;
use App\Models\StarCitizen\ShipMatrix\Vehicle\Vehicle;

it('searches vehicles by name', function (): void {
    $manufacturer = Manufacturer::query()->create([
        'cig_id' => 1,
        'name' => 'Aegis Dynamics',
        'name_short' => 'AEGS',
    ]);

    $size = Size::query()->create(['slug' => 'small']);
    $type = Type::query()->create(['slug' => 'fighter']);
    $note = ProductionNote::query()->create([
        'translation' => ['en' => 'Test note'],
    ]);

    $status = ProductionStatus::query()->create(['slug' => 'flight-ready']);

    Vehicle::query()->create([
        'cig_id' => 1,
        'name' => 'Avenger Titan',
        'slug' => 'avenger-titan',
        'manufacturer_id' => $manufacturer->id,
        'size_id' => $size->id,
        'type_id' => $type->id,
        'production_status_id' => $status->id,
        'production_note_id' => $note->id,
        'chassis_id' => 1,
    ]);

    Vehicle::query()->create([
        'cig_id' => 2,
        'name' => 'Avenger Stalker',
        'slug' => 'avenger-stalker',
        'manufacturer_id' => $manufacturer->id,
        'size_id' => $size->id,
        'type_id' => $type->id,
        'production_status_id' => $status->id,
        'production_note_id' => $note->id,
        'chassis_id' => 1,
    ]);

    Vehicle::query()->create([
        'cig_id' => 3,
        'name' => 'Gladius',
        'slug' => 'gladius',
        'manufacturer_id' => $manufacturer->id,
        'size_id' => $size->id,
        'type_id' => $type->id,
        'production_status_id' => $status->id,
        'production_note_id' => $note->id,
        'chassis_id' => 1,
    ]);

    $response = $this->postJson(route('shipmatrix.vehicles.search'), [
        'query' => 'Avenger',
    ]);

    $response->assertOk();
    expect($response->json('data'))->toHaveCount(2);
    expect($response->json('data.0.name'))->toContain('Avenger');
    expect($response->json('data.1.name'))->toContain('Avenger');
});

it('returns empty results when no results found', function (): void {
    $manufacturer = Manufacturer::query()->create([
        'cig_id' => 1,
        'name' => 'Test Manufacturer',
        'name_short' => 'TEST',
    ]);

    $size = Size::query()->create(['slug' => 'small']);
    $type = Type::query()->create(['slug' => 'fighter']);
    $note = ProductionNote::query()->create([
        'translation' => ['en' => 'Test note'],
    ]);

    $status = ProductionStatus::query()->create(['slug' => 'flight-ready']);

    Vehicle::query()->create([
        'cig_id' => 1,
        'name' => 'Gladius',
        'slug' => 'gladius',
        'manufacturer_id' => $manufacturer->id,
        'size_id' => $size->id,
        'type_id' => $type->id,
        'production_status_id' => $status->id,
        'production_note_id' => $note->id,
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
    $manufacturer = Manufacturer::query()->create([
        'cig_id' => 1,
        'name' => 'Test Manufacturer',
        'name_short' => 'TEST',
    ]);

    $size = Size::query()->create(['slug' => 'small']);
    $type = Type::query()->create(['slug' => 'fighter']);
    $note = ProductionNote::query()->create([
        'translation' => ['en' => 'Test note'],
    ]);

    $status = ProductionStatus::query()->create(['slug' => 'flight-ready']);

    // Create 20 vehicles matching the search
    for ($i = 1; $i <= 20; $i++) {
        Vehicle::query()->create([
            'cig_id' => $i,
            'name' => "Test Vehicle {$i}",
            'slug' => "test-vehicle-{$i}",
            'manufacturer_id' => $manufacturer->id,
            'size_id' => $size->id,
            'type_id' => $type->id,
            'production_status_id' => $status->id,
            'production_note_id' => $note->id,
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
    $aegis = Manufacturer::query()->create([
        'cig_id' => 1,
        'name' => 'Aegis Dynamics',
        'name_short' => 'AEGS',
    ]);

    $drake = Manufacturer::query()->create([
        'cig_id' => 2,
        'name' => 'Drake Interplanetary',
        'name_short' => 'DRAK',
    ]);

    $size = Size::query()->create(['slug' => 'small']);
    $type = Type::query()->create(['slug' => 'fighter']);
    $note = ProductionNote::query()->create([
        'translation' => ['en' => 'Test note'],
    ]);

    $status = ProductionStatus::query()->create(['slug' => 'flight-ready']);

    Vehicle::query()->create([
        'cig_id' => 1,
        'name' => 'Combat Fighter Alpha',
        'slug' => 'combat-fighter-alpha',
        'manufacturer_id' => $aegis->id,
        'size_id' => $size->id,
        'type_id' => $type->id,
        'production_status_id' => $status->id,
        'production_note_id' => $note->id,
        'chassis_id' => 1,
    ]);

    Vehicle::query()->create([
        'cig_id' => 2,
        'name' => 'Combat Fighter Beta',
        'slug' => 'combat-fighter-beta',
        'manufacturer_id' => $drake->id,
        'size_id' => $size->id,
        'type_id' => $type->id,
        'production_status_id' => $status->id,
        'production_note_id' => $note->id,
        'chassis_id' => 1,
    ]);

    $response = $this->postJson(route('shipmatrix.vehicles.search', ['filter' => ['manufacturer' => 'Aegis Dynamics']]), [
        'query' => 'Combat',
    ]);

    $response->assertOk();
    expect($response->json('data'))->toHaveCount(1);
    expect($response->json('data.0.name'))->toBe('Combat Fighter Alpha');
});
