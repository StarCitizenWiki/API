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

    $status = ProductionStatus::query()->create([
        'slug' => 'flight-ready',
    ]);

    $note = ProductionNote::query()->create([
        'translation' => ['en' => 'Test note'],
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

it('paginates vehicles by requested page size and sort order', function (): void {
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

    foreach ([70, 10, 50, 20, 60, 30, 40] as $cigId) {
        Vehicle::query()->create([
            'cig_id' => $cigId,
            'name' => "Vehicle {$cigId}",
            'slug' => "vehicle-{$cigId}",
            'manufacturer_id' => $manufacturer->id,
            'size_id' => $size->id,
            'type_id' => $type->id,
            'production_status_id' => $status->id,
            'production_note_id' => $note->id,
            'chassis_id' => 1,
        ]);
    }

    $response = $this->getJson(route('shipmatrix.vehicles.index', [
        'sort' => 'id',
        'page' => [
            'number' => 2,
            'size' => 3,
        ],
    ]));

    $response->assertOk()
        ->assertJsonPath('meta.current_page', 2)
        ->assertJsonPath('meta.per_page', 3)
        ->assertJsonPath('meta.total', 7)
        ->assertJsonPath('meta.last_page', 3);

    expect(collect($response->json('data'))->pluck('id')->all())->toBe([40, 50, 60]);
});

it('builds pagination links for the requested page size', function (): void {
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

    foreach (range(1, 12) as $i) {
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

    expect($response->json('meta.current_page'))->toBe(2)
        ->and($response->json('meta.per_page'))->toBe(5)
        ->and($response->json('meta.total'))->toBe(12)
        ->and($response->json('meta.last_page'))->toBe(3)
        ->and(collect($response->json('data'))->pluck('id')->all())->toBe([6, 7, 8, 9, 10]);

    $prevLink = $response->json('links.prev');
    $nextLink = $response->json('links.next');
    $lastLink = $response->json('links.last');

    expect($prevLink)->toBeString();
    expect($nextLink)->toBeString();
    expect($lastLink)->toBeString();
    expect($prevLink)->toContain('page%5Bnumber%5D=1');
    expect($nextLink)->toContain('page%5Bnumber%5D=3');
    expect($lastLink)->toContain('page%5Bsize%5D=5');
    expect(substr_count($lastLink, 'page%5Bnumber%5D='))->toBe(1);
});

it('uses the default page size when none is requested', function (): void {
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

    for ($i = 1; $i <= 31; $i++) {
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

    $response->assertOk()
        ->assertJsonPath('meta.current_page', 1)
        ->assertJsonPath('meta.per_page', 30)
        ->assertJsonPath('meta.total', 31)
        ->assertJsonPath('meta.last_page', 2)
        ->assertJsonCount(30, 'data');

    $secondPageResponse = $this->getJson(route('shipmatrix.vehicles.index', [
        'page' => [
            'number' => 2,
        ],
    ]));

    $secondPageResponse->assertOk()
        ->assertJsonPath('meta.current_page', 2)
        ->assertJsonPath('meta.per_page', 30)
        ->assertJsonPath('meta.total', 31)
        ->assertJsonPath('meta.last_page', 2)
        ->assertJsonCount(1, 'data');
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
        'translation' => ['en' => 'Test note'],
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
    expect($response->json('data'))->toHaveCount(1)
        ->and($response->json('data.0.name'))->toBe('Avenger');
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
        'translation' => ['en' => 'Test note'],
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
    expect($response->json('data'))->toHaveCount(1)
        ->and($response->json('data.0.name'))->toBe('Small Ship');
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
        'translation' => ['en' => 'Test note'],
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
        'translation' => ['en' => 'Test note'],
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
        'translation' => ['en' => 'Test note'],
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
    expect($response->json('data'))->toHaveCount(1)
        ->and($response->json('data.0.name'))->toBe('Ready Ship');
});

it('returns mapped vehicle fields and supports filtering by partial name', function (): void {
    $manufacturer = Manufacturer::query()->create([
        'cig_id' => 1,
        'name' => 'Aegis Dynamics',
        'name_short' => 'AEGS',
    ]);

    $size = Size::query()->create([
        'slug' => 'small',
        'translation' => ['en' => 'Small'],
    ]);
    $type = Type::query()->create([
        'slug' => 'fighter',
        'translation' => ['en' => 'Fighter'],
    ]);
    $note = ProductionNote::query()->create([
        'translation' => ['en' => 'In active production'],
    ]);
    $status = ProductionStatus::query()->create([
        'slug' => 'flight-ready',
        'translation' => ['en' => 'Flight Ready'],
    ]);

    $focus = Focus::query()->create([
        'slug' => 'combat',
        'translation' => ['en' => 'Combat'],
    ]);

    $vehicle = Vehicle::query()->create([
        'cig_id' => 1,
        'name' => 'Avenger Mk II',
        'slug' => 'avenger-mk-ii',
        'manufacturer_id' => $manufacturer->id,
        'size_id' => $size->id,
        'type_id' => $type->id,
        'production_status_id' => $status->id,
        'production_note_id' => $note->id,
        'chassis_id' => 1,
        'length' => 22.5,
        'beam' => 6.75,
        'height' => 4.25,
        'mass' => 54321,
        'cargo_capacity' => 8,
        'min_crew' => 1,
        'max_crew' => 2,
        'scm_speed' => 210,
        'afterburner_speed' => 1200,
        'msrp' => 1250000,
        'pledge_url' => '/pledge/ships/avenger',
        'translation' => ['en' => 'Light combat ship'],
    ]);
    $vehicle->foci()->attach($focus->id);

    Vehicle::query()->create([
        'cig_id' => 2,
        'name' => 'Cutter Scout',
        'slug' => 'cutter-scout',
        'manufacturer_id' => $manufacturer->id,
        'size_id' => $size->id,
        'type_id' => $type->id,
        'production_status_id' => $status->id,
        'production_note_id' => $note->id,
        'chassis_id' => 2,
    ]);

    $response = $this->getJson(route('shipmatrix.vehicles.index', [
        'locale' => 'en',
        'filter' => ['name' => 'Aveng'],
    ]));

    $response->assertOk()
        ->assertJsonPath('meta.total', 1)
        ->assertJsonPath('data.0.id', 1)
        ->assertJsonPath('data.0.chassis_id', 1)
        ->assertJsonPath('data.0.name', 'Avenger Mk II')
        ->assertJsonPath('data.0.slug', 'avenger-mk-ii')
        ->assertJsonPath('data.0.dimension.length', 22.5)
        ->assertJsonPath('data.0.dimension.width', 6.75)
        ->assertJsonPath('data.0.dimension.height', 4.25)
        ->assertJsonPath('data.0.crew.min', 1)
        ->assertJsonPath('data.0.crew.max', 2)
        ->assertJsonPath('data.0.speed.scm', 210)
        ->assertJsonPath('data.0.speed.max', 1200)
        ->assertJsonPath('data.0.type', 'Fighter')
        ->assertJsonPath('data.0.size', 'Small')
        ->assertJsonPath('data.0.production_status', 'Flight Ready')
        ->assertJsonPath('data.0.production_note', 'In active production')
        ->assertJsonPath('data.0.description', 'Light combat ship')
        ->assertJsonPath('data.0.msrp', 1250000)
        ->assertJsonPath('data.0.pledge_url', 'https://robertsspaceindustries.com/pledge/ships/avenger')
        ->assertJsonPath('data.0.manufacturer.code', 'AEGS')
        ->assertJsonPath('data.0.manufacturer.name', 'Aegis Dynamics')
        ->assertJsonPath('data.0.link', route('shipmatrix.vehicles.show', 'avenger-mk-ii'));

    expect($response->json('data.0.foci'))->toBe(['Combat']);
});
