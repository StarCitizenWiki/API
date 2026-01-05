<?php

declare(strict_types=1);

use App\Models\StarCitizen\ShipMatrix\Manufacturer;
use App\Models\StarCitizen\ShipMatrix\ProductionNote;
use App\Models\StarCitizen\ShipMatrix\ProductionStatus;
use App\Models\StarCitizen\ShipMatrix\Vehicle\Focus;
use App\Models\StarCitizen\ShipMatrix\Vehicle\Size;
use App\Models\StarCitizen\ShipMatrix\Vehicle\Type;
use App\Models\StarCitizen\ShipMatrix\Vehicle\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns the vehicle list without error', function (): void {
    $manufacturer = Manufacturer::query()->create([
        'cig_id' => 1,
        'name' => 'Aegis Dynamics',
        'name_short' => 'AEGS',
    ]);

    $size = Size::query()->create([
        'slug' => 'small',
    ]);

    $type = Type::query()->create([
        'slug' => 'fighter',
    ]);

    $note = ProductionNote::query()->create([
        'content_hash' => 'test-hash',
    ]);

    $status = ProductionStatus::query()->create([
        'slug' => 'flight-ready',
    ]);

    $note = ProductionNote::query()->create([
        'content_hash' => 'test-hash',
    ]);

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
    ]);

    $response = $this->getJson(route('shipmatrix.vehicles.index'));

    $response->assertOk();
    expect($response->json('data.0.name'))->toBe('Avenger');
    expect($response->json('data.0.slug'))->toBe('avenger');
});

it('returns paginated results', function (): void {
    $manufacturer = Manufacturer::query()->create([
        'cig_id' => 1,
        'name' => 'Test Manufacturer',
        'name_short' => 'TEST',
    ]);

    $size = Size::query()->create(['slug' => 'small']);
    $type = Type::query()->create(['slug' => 'fighter']);
    $note = ProductionNote::query()->create([
        'content_hash' => 'test-hash',
    ]);

    $status = ProductionStatus::query()->create(['slug' => 'flight-ready']);

    // Create 20 vehicles
    for ($i = 1; $i <= 20; $i++) {
        Vehicle::query()->create([
            'cig_id' => $i,
            'name' => "Vehicle {$i}",
            'slug' => "vehicle-{$i}",
            'manufacturer_id' => $manufacturer->id,
            'size_id' => $size->id,
            'type_id' => $type->id,
            'production_status_id' => $status->id,
            'production_note_id' => $note->id,
            'chassis_id' => 1,
        ]);
    }

    $response = $this->getJson(route('shipmatrix.vehicles.index'));

    $response->assertOk();
    expect($response->json('data'))->toHaveCount(20); // Fits within the JSON:API default page size.
    expect($response->json('meta.total'))->toBe(20);
});

it('does not duplicate page number in pagination links', function (): void {
    $manufacturer = Manufacturer::query()->create([
        'cig_id' => 1,
        'name' => 'Test Manufacturer',
        'name_short' => 'TEST',
    ]);

    $size = Size::query()->create(['slug' => 'small']);
    $type = Type::query()->create(['slug' => 'fighter']);
    $note = ProductionNote::query()->create([
        'content_hash' => 'test-hash',
    ]);
    $status = ProductionStatus::query()->create(['slug' => 'flight-ready']);

    for ($i = 1; $i <= 12; $i++) {
        Vehicle::query()->create([
            'cig_id' => $i,
            'name' => "Vehicle {$i}",
            'slug' => "vehicle-{$i}",
            'manufacturer_id' => $manufacturer->id,
            'size_id' => $size->id,
            'type_id' => $type->id,
            'production_status_id' => $status->id,
            'production_note_id' => $note->id,
            'chassis_id' => 1,
        ]);
    }

    $response = $this->getJson(route('shipmatrix.vehicles.index', [
        'page' => [
            'number' => 2,
            'size' => 5,
        ],
    ]));

    $response->assertOk();

    $lastLink = $response->json('links.last');

    expect($lastLink)->toBeString();
    expect($lastLink)->toContain('page%5Bsize%5D=5');
    expect(substr_count($lastLink, 'page%5Bnumber%5D='))->toBe(1);
});

it('ignores custom pagination limit', function (): void {
    $manufacturer = Manufacturer::query()->create([
        'cig_id' => 1,
        'name' => 'Test Manufacturer',
        'name_short' => 'TEST',
    ]);

    $size = Size::query()->create(['slug' => 'small']);
    $type = Type::query()->create(['slug' => 'fighter']);
    $note = ProductionNote::query()->create([
        'content_hash' => 'test-hash',
    ]);

    $status = ProductionStatus::query()->create(['slug' => 'flight-ready']);

    for ($i = 1; $i <= 20; $i++) {
        Vehicle::query()->create([
            'cig_id' => $i,
            'name' => "Vehicle {$i}",
            'slug' => "vehicle-{$i}",
            'manufacturer_id' => $manufacturer->id,
            'size_id' => $size->id,
            'type_id' => $type->id,
            'production_status_id' => $status->id,
            'production_note_id' => $note->id,
            'chassis_id' => 1,
        ]);
    }

    $response = $this->getJson(route('shipmatrix.vehicles.index', ['limit' => 1]));

    $response->assertOk();
    expect($response->json('data'))->toHaveCount(20);
});

it('filters by manufacturer name', function (): void {
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
        'content_hash' => 'test-hash',
    ]);

    $status = ProductionStatus::query()->create(['slug' => 'flight-ready']);

    Vehicle::query()->create([
        'cig_id' => 1,
        'name' => 'Avenger',
        'slug' => 'avenger',
        'manufacturer_id' => $aegis->id,
        'size_id' => $size->id,
        'type_id' => $type->id,
        'production_status_id' => $status->id,
        'production_note_id' => $note->id,
        'chassis_id' => 1,
    ]);

    Vehicle::query()->create([
        'cig_id' => 2,
        'name' => 'Cutlass',
        'slug' => 'cutlass',
        'manufacturer_id' => $drake->id,
        'size_id' => $size->id,
        'type_id' => $type->id,
        'production_status_id' => $status->id,
        'production_note_id' => $note->id,
        'chassis_id' => 1,
    ]);

    $response = $this->getJson(route('shipmatrix.vehicles.index', ['filter' => ['manufacturer' => 'Aegis Dynamics']]));

    $response->assertOk();
    expect($response->json('data'))->toHaveCount(1);
    expect($response->json('data.0.name'))->toBe('Avenger');
});

it('filters by size code', function (): void {
    $manufacturer = Manufacturer::query()->create([
        'cig_id' => 1,
        'name' => 'Test Manufacturer',
        'name_short' => 'TEST',
    ]);

    $small = Size::query()->create(['slug' => 'small']);
    $large = Size::query()->create(['slug' => 'large']);
    $type = Type::query()->create(['slug' => 'fighter']);
    $note = ProductionNote::query()->create([
        'content_hash' => 'test-hash',
    ]);

    $status = ProductionStatus::query()->create(['slug' => 'flight-ready']);

    Vehicle::query()->create([
        'cig_id' => 1,
        'name' => 'Small Ship',
        'slug' => 'small-ship',
        'manufacturer_id' => $manufacturer->id,
        'size_id' => $small->id,
        'type_id' => $type->id,
        'production_status_id' => $status->id,
        'production_note_id' => $note->id,
        'chassis_id' => 1,
    ]);

    Vehicle::query()->create([
        'cig_id' => 2,
        'name' => 'Large Ship',
        'slug' => 'large-ship',
        'manufacturer_id' => $manufacturer->id,
        'size_id' => $large->id,
        'type_id' => $type->id,
        'production_status_id' => $status->id,
        'production_note_id' => $note->id,
        'chassis_id' => 1,
    ]);

    $response = $this->getJson(route('shipmatrix.vehicles.index', ['filter' => ['size' => 'small']]));

    $response->assertOk();
    expect($response->json('data'))->toHaveCount(1);
    expect($response->json('data.0.name'))->toBe('Small Ship');
});

it('filters by type slug', function (): void {
    $manufacturer = Manufacturer::query()->create([
        'cig_id' => 1,
        'name' => 'Test Manufacturer',
        'name_short' => 'TEST',
    ]);

    $size = Size::query()->create(['slug' => 'small']);
    $fighter = Type::query()->create(['slug' => 'fighter']);
    $transport = Type::query()->create(['slug' => 'transport']);
    $note = ProductionNote::query()->create([
        'content_hash' => 'test-hash',
    ]);

    $status = ProductionStatus::query()->create(['slug' => 'flight-ready']);

    Vehicle::query()->create([
        'cig_id' => 1,
        'name' => 'Fighter Ship',
        'slug' => 'fighter-ship',
        'manufacturer_id' => $manufacturer->id,
        'size_id' => $size->id,
        'type_id' => $fighter->id,
        'production_status_id' => $status->id,
        'production_note_id' => $note->id,
        'chassis_id' => 1,
    ]);

    Vehicle::query()->create([
        'cig_id' => 2,
        'name' => 'Transport Ship',
        'slug' => 'transport-ship',
        'manufacturer_id' => $manufacturer->id,
        'size_id' => $size->id,
        'type_id' => $transport->id,
        'production_status_id' => $status->id,
        'production_note_id' => $note->id,
        'chassis_id' => 1,
    ]);

    $response = $this->getJson(route('shipmatrix.vehicles.index', ['filter' => ['type' => 'fighter']]));

    $response->assertOk();
    expect($response->json('data'))->toHaveCount(1);
    expect($response->json('data.0.name'))->toBe('Fighter Ship');
});

it('filters by focus slug', function (): void {
    $manufacturer = Manufacturer::query()->create([
        'cig_id' => 1,
        'name' => 'Test Manufacturer',
        'name_short' => 'TEST',
    ]);

    $size = Size::query()->create(['slug' => 'small']);
    $type = Type::query()->create(['slug' => 'multi-role']);
    $note = ProductionNote::query()->create([
        'content_hash' => 'test-hash',
    ]);

    $status = ProductionStatus::query()->create(['slug' => 'flight-ready']);

    $combat = Focus::query()->create(['slug' => 'combat']);
    $exploration = Focus::query()->create(['slug' => 'exploration']);

    $vehicle1 = Vehicle::query()->create([
        'cig_id' => 1,
        'name' => 'Combat Ship',
        'slug' => 'combat-ship',
        'manufacturer_id' => $manufacturer->id,
        'size_id' => $size->id,
        'type_id' => $type->id,
        'production_status_id' => $status->id,
        'production_note_id' => $note->id,
        'chassis_id' => 1,
    ]);

    $vehicle2 = Vehicle::query()->create([
        'cig_id' => 2,
        'name' => 'Explorer Ship',
        'slug' => 'explorer-ship',
        'manufacturer_id' => $manufacturer->id,
        'size_id' => $size->id,
        'type_id' => $type->id,
        'production_status_id' => $status->id,
        'production_note_id' => $note->id,
        'chassis_id' => 1,
    ]);

    $vehicle1->foci()->attach($combat->id);
    $vehicle2->foci()->attach($exploration->id);

    $response = $this->getJson(route('shipmatrix.vehicles.index', ['filter' => ['focus' => 'combat']]));

    $response->assertOk();
    expect($response->json('data'))->toHaveCount(1);
    expect($response->json('data.0.name'))->toBe('Combat Ship');
});

it('filters by production status slug', function (): void {
    $manufacturer = Manufacturer::query()->create([
        'cig_id' => 1,
        'name' => 'Test Manufacturer',
        'name_short' => 'TEST',
    ]);

    $size = Size::query()->create(['slug' => 'small']);
    $type = Type::query()->create(['slug' => 'fighter']);
    $note = ProductionNote::query()->create([
        'content_hash' => 'test-hash',
    ]);
    $flightReady = ProductionStatus::query()->create(['slug' => 'flight-ready']);
    $inDevelopment = ProductionStatus::query()->create(['slug' => 'in-development']);

    Vehicle::query()->create([
        'cig_id' => 1,
        'name' => 'Ready Ship',
        'slug' => 'ready-ship',
        'manufacturer_id' => $manufacturer->id,
        'size_id' => $size->id,
        'type_id' => $type->id,
        'production_status_id' => $flightReady->id,
        'production_note_id' => $note->id,
        'chassis_id' => 1,
    ]);

    Vehicle::query()->create([
        'cig_id' => 2,
        'name' => 'Dev Ship',
        'slug' => 'dev-ship',
        'manufacturer_id' => $manufacturer->id,
        'size_id' => $size->id,
        'type_id' => $type->id,
        'production_status_id' => $inDevelopment->id,
        'production_note_id' => $note->id,
        'chassis_id' => 2,
    ]);

    $response = $this->getJson(route('shipmatrix.vehicles.index', ['filter' => ['production_status' => 'flight-ready']]));

    $response->assertOk();
    expect($response->json('data'))->toHaveCount(1);
    expect($response->json('data.0.name'))->toBe('Ready Ship');
});

it('has correct response structure', function (): void {
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
    ]);

    $response = $this->getJson(route('shipmatrix.vehicles.index'));

    $response->assertOk();
    $response->assertJsonStructure([
        'data' => [
            '*' => [
                'id',
                'name',
                'slug',
                'size',
                'type',
                'manufacturer' => [
                    'name',
                    'code',
                ],
                'production_status',
                'updated_at',
            ],
        ],
        'meta',
    ]);
});
