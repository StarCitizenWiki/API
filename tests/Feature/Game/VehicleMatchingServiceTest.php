<?php

declare(strict_types=1);

use App\Models\StarCitizen\Manufacturer\Manufacturer as ShipMatrixManufacturer;
use App\Models\StarCitizen\ProductionNote\ProductionNote;
use App\Models\StarCitizen\ProductionStatus\ProductionStatus;
use App\Models\StarCitizen\Vehicle\Size\Size as ShipSize;
use App\Models\StarCitizen\Vehicle\Type\Type as ShipType;
use App\Models\StarCitizen\Vehicle\Vehicle\Vehicle as ShipMatrixVehicle;
use App\Services\Game\VehicleMatchingService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    // Create required reference data for ship matrix vehicles
    $this->productionStatus = ProductionStatus::query()->create([
        'name' => 'In Production',
        'slug' => 'in-production',
    ]);

    $this->productionNote = ProductionNote::query()->create([
        'note' => 'None',
        'slug' => 'none',
    ]);

    $this->size = ShipSize::query()->create([
        'slug' => 'small',
        'size' => 'Small',
    ]);

    $this->type = ShipType::query()->create([
        'slug' => 'fighter',
        'type' => 'Fighter',
    ]);

    $this->manufacturer = ShipMatrixManufacturer::query()->create([
        'cig_id' => 1,
        'name' => 'Anvil Aerospace',
        'name_short' => 'ANV',
        'slug' => 'anvil-aerospace',
    ]);

    $this->service = app(VehicleMatchingService::class);
});

it('finds exact match by name', function (): void {
    $vehicle = ShipMatrixVehicle::query()->create([
        'cig_id' => 1,
        'name' => 'Hornet F7C',
        'slug' => 'hornet-f7c',
        'manufacturer_id' => $this->manufacturer->id,
        'production_status_id' => $this->productionStatus->id,
        'production_note_id' => $this->productionNote->id,
        'size_id' => $this->size->id,
        'type_id' => $this->type->id,
        'chassis_id' => 1,
    ]);

    $payload = [
        'UUID' => 'test-uuid',
        'Name' => 'Hornet F7C',
        'ClassName' => 'TEST_CLASS',
        'Manufacturer' => [
            'Name' => 'Anvil Aerospace',
            'Code' => 'ANV',
        ],
    ];

    $result = $this->service->findMatch($payload);

    expect($result)->toBe($vehicle->id);
});

it('finds match with manufacturer prefix stripped', function (): void {
    $vehicle = ShipMatrixVehicle::query()->create([
        'cig_id' => 2,
        'name' => 'F7C Hornet',
        'slug' => 'f7c-hornet',
        'manufacturer_id' => $this->manufacturer->id,
        'production_status_id' => $this->productionStatus->id,
        'production_note_id' => $this->productionNote->id,
        'size_id' => $this->size->id,
        'type_id' => $this->type->id,
        'chassis_id' => 2,
    ]);

    $payload = [
        'UUID' => 'test-uuid',
        'Name' => 'Anvil F7C Hornet',
        'ClassName' => 'TEST_CLASS',
        'Manufacturer' => [
            'Name' => 'Anvil Aerospace',
            'Code' => 'ANV',
        ],
    ];

    $result = $this->service->findMatch($payload);

    expect($result)->toBe($vehicle->id);
});

it('uses config overrides for matching', function (): void {
    config(['game.vehicle_name_overrides' => [
        'Difficult Name' => 'Easy Name',
    ]]);

    $vehicle = ShipMatrixVehicle::query()->create([
        'cig_id' => 3,
        'name' => 'Easy Name',
        'slug' => 'easy-name',
        'manufacturer_id' => $this->manufacturer->id,
        'production_status_id' => $this->productionStatus->id,
        'production_note_id' => $this->productionNote->id,
        'size_id' => $this->size->id,
        'type_id' => $this->type->id,
        'chassis_id' => 3,
    ]);

    $payload = [
        'UUID' => 'test-uuid',
        'Name' => 'Difficult Name',
        'ClassName' => 'TEST_CLASS',
        'Manufacturer' => [
            'Name' => 'Anvil Aerospace',
            'Code' => 'ANV',
        ],
    ];

    $result = $this->service->findMatch($payload);

    expect($result)->toBe($vehicle->id);
});

it('performs fuzzy matching within Levenshtein threshold', function (): void {
    $vehicle = ShipMatrixVehicle::query()->create([
        'cig_id' => 4,
        'name' => 'Gladius',
        'slug' => 'gladius',
        'manufacturer_id' => $this->manufacturer->id,
        'production_status_id' => $this->productionStatus->id,
        'production_note_id' => $this->productionNote->id,
        'size_id' => $this->size->id,
        'type_id' => $this->type->id,
        'chassis_id' => 4,
    ]);

    $payload = [
        'UUID' => 'test-uuid',
        'Name' => 'Gladios', // Off by 2 characters (Levenshtein distance = 2)
        'ClassName' => 'TEST_CLASS',
        'Manufacturer' => [
            'Name' => 'Anvil Aerospace',
            'Code' => 'ANV',
        ],
    ];

    $result = $this->service->findMatch($payload);

    expect($result)->toBe($vehicle->id);
});

it('returns null when no match found', function (): void {
    $payload = [
        'UUID' => 'test-uuid',
        'Name' => 'NonExistent Vehicle',
        'ClassName' => 'TEST_CLASS',
        'Manufacturer' => [
            'Name' => 'Anvil Aerospace',
            'Code' => 'ANV',
        ],
    ];

    $result = $this->service->findMatch($payload);

    expect($result)->toBeNull();
});

it('matches without manufacturer constraint as fallback', function (): void {
    $otherManufacturer = ShipMatrixManufacturer::query()->create([
        'cig_id' => 2,
        'name' => 'Origin Jumpworks',
        'name_short' => 'ORIG',
        'slug' => 'origin-jumpworks',
    ]);

    $vehicle = ShipMatrixVehicle::query()->create([
        'cig_id' => 5,
        'name' => '300i',
        'slug' => '300i',
        'manufacturer_id' => $otherManufacturer->id,
        'production_status_id' => $this->productionStatus->id,
        'production_note_id' => $this->productionNote->id,
        'size_id' => $this->size->id,
        'type_id' => $this->type->id,
        'chassis_id' => 5,
    ]);

    $payload = [
        'UUID' => 'test-uuid',
        'Name' => '300i',
        'ClassName' => 'TEST_CLASS',
        'Manufacturer' => [
            'Name' => 'Anvil Aerospace', // Wrong manufacturer
            'Code' => 'ANV',
        ],
    ];

    $result = $this->service->findMatch($payload);

    // Should still find it without manufacturer constraint
    expect($result)->toBe($vehicle->id);
});

it('handles special manufacturer abbreviations', function (): void {
    $rsi = ShipMatrixManufacturer::query()->create([
        'cig_id' => 3,
        'name' => 'Roberts Space Industries',
        'name_short' => 'RSI',
        'slug' => 'roberts-space-industries',
    ]);

    $vehicle = ShipMatrixVehicle::query()->create([
        'cig_id' => 6,
        'name' => 'Aurora',
        'slug' => 'aurora',
        'manufacturer_id' => $rsi->id,
        'production_status_id' => $this->productionStatus->id,
        'production_note_id' => $this->productionNote->id,
        'size_id' => $this->size->id,
        'type_id' => $this->type->id,
        'chassis_id' => 6,
    ]);

    $payload = [
        'UUID' => 'test-uuid',
        'Name' => 'RSI Aurora',
        'ClassName' => 'TEST_CLASS',
        'Manufacturer' => [
            'Name' => 'Roberts Space Industries',
            'Code' => 'RSI',
        ],
    ];

    $result = $this->service->findMatch($payload);

    expect($result)->toBe($vehicle->id);
});

it('matches by slug when name differs', function (): void {
    $vehicle = ShipMatrixVehicle::query()->create([
        'cig_id' => 7,
        'name' => 'Cutlass Black',
        'slug' => 'cutlass-black',
        'manufacturer_id' => $this->manufacturer->id,
        'production_status_id' => $this->productionStatus->id,
        'production_note_id' => $this->productionNote->id,
        'size_id' => $this->size->id,
        'type_id' => $this->type->id,
        'chassis_id' => 7,
    ]);

    $payload = [
        'UUID' => 'test-uuid',
        'Name' => 'Cutlass_Black', // Underscore instead of space
        'ClassName' => 'TEST_CLASS',
        'Manufacturer' => [
            'Name' => 'Anvil Aerospace',
            'Code' => 'ANV',
        ],
    ];

    $result = $this->service->findMatch($payload);

    expect($result)->toBe($vehicle->id);
});

it('strips Wikelo variant suffixes and matches base model', function (): void {
    $vehicle = ShipMatrixVehicle::query()->create([
        'cig_id' => 8,
        'name' => 'Sabre Firebird',
        'slug' => 'sabre-firebird',
        'manufacturer_id' => $this->manufacturer->id,
        'production_status_id' => $this->productionStatus->id,
        'production_note_id' => $this->productionNote->id,
        'size_id' => $this->size->id,
        'type_id' => $this->type->id,
        'chassis_id' => 8,
    ]);

    $payload = [
        'UUID' => 'test-uuid',
        'Name' => 'Anvil Sabre Firebird Wikelo War Special', // Has Wikelo War Special suffix
        'ClassName' => 'TEST_CLASS',
        'Manufacturer' => [
            'Name' => 'Anvil Aerospace',
            'Code' => 'ANV',
        ],
    ];

    $result = $this->service->findMatch($payload);

    expect($result)->toBe($vehicle->id);
});

it('strips PYAM Exec suffix and matches base model', function (): void {
    $vehicle = ShipMatrixVehicle::query()->create([
        'cig_id' => 9,
        'name' => 'F8C Lightning',
        'slug' => 'f8c-lightning',
        'manufacturer_id' => $this->manufacturer->id,
        'production_status_id' => $this->productionStatus->id,
        'production_note_id' => $this->productionNote->id,
        'size_id' => $this->size->id,
        'type_id' => $this->type->id,
        'chassis_id' => 9,
    ]);

    $payload = [
        'UUID' => 'test-uuid',
        'Name' => 'F8C Lightning PYAM Exec', // Has PYAM Exec suffix
        'ClassName' => 'TEST_CLASS',
        'Manufacturer' => [
            'Name' => 'Anvil Aerospace',
            'Code' => 'ANV',
        ],
    ];

    $result = $this->service->findMatch($payload);

    expect($result)->toBe($vehicle->id);
});

it('reorders Best In Show edition names to match', function (): void {
    $aegis = ShipMatrixManufacturer::query()->create([
        'cig_id' => 4,
        'name' => 'Aegis Dynamics',
        'name_short' => 'AEGS',
        'slug' => 'aegis-dynamics',
    ]);

    $vehicle = ShipMatrixVehicle::query()->create([
        'cig_id' => 10,
        'name' => 'Hammerhead Best In Show Edition 2949',
        'slug' => 'hammerhead-best-in-show-edition-2949',
        'manufacturer_id' => $aegis->id,
        'production_status_id' => $this->productionStatus->id,
        'production_note_id' => $this->productionNote->id,
        'size_id' => $this->size->id,
        'type_id' => $this->type->id,
        'chassis_id' => 10,
    ]);

    $payload = [
        'UUID' => 'test-uuid',
        'Name' => 'Aegis Hammerhead 2949 Best In Show Edition', // Year before edition text
        'ClassName' => 'TEST_CLASS',
        'Manufacturer' => [
            'Name' => 'Aegis Dynamics',
            'Code' => 'AEGS',
        ],
    ];

    $result = $this->service->findMatch($payload);

    expect($result)->toBe($vehicle->id);
});

it('strips color variant suffixes and matches base model', function (): void {
    $argo = ShipMatrixManufacturer::query()->create([
        'cig_id' => 5,
        'name' => 'Argo Astronautics',
        'name_short' => 'ARGO',
        'slug' => 'argo-astronautics',
    ]);

    $vehicle = ShipMatrixVehicle::query()->create([
        'cig_id' => 11,
        'name' => 'ATLS GEO',
        'slug' => 'atls-geo',
        'manufacturer_id' => $argo->id,
        'production_status_id' => $this->productionStatus->id,
        'production_note_id' => $this->productionNote->id,
        'size_id' => $this->size->id,
        'type_id' => $this->type->id,
        'chassis_id' => 11,
    ]);

    $payload = [
        'UUID' => 'test-uuid',
        'Name' => 'ATLS Snowland Color', // Has color variant suffix
        'ClassName' => 'TEST_CLASS',
        'Manufacturer' => [
            'Name' => 'Argo Astronautics',
            'Code' => 'ARGO',
        ],
    ];

    $result = $this->service->findMatch($payload);

    expect($result)->toBe($vehicle->id);
});

it('uses config override for Hornet Heartseeker variant', function (): void {
    config(['game.vehicle_name_overrides' => [
        'Anvil F7C-M Hornet Heartseeker Mk I' => 'F7C-M Super Hornet Heartseeker Mk I',
    ]]);

    $vehicle = ShipMatrixVehicle::query()->create([
        'cig_id' => 12,
        'name' => 'F7C-M Super Hornet Heartseeker Mk I',
        'slug' => 'f7c-m-super-hornet-heartseeker-mk-i',
        'manufacturer_id' => $this->manufacturer->id,
        'production_status_id' => $this->productionStatus->id,
        'production_note_id' => $this->productionNote->id,
        'size_id' => $this->size->id,
        'type_id' => $this->type->id,
        'chassis_id' => 12,
    ]);

    $payload = [
        'UUID' => 'test-uuid',
        'Name' => 'Anvil F7C-M Hornet Heartseeker Mk I', // Missing "Super"
        'ClassName' => 'TEST_CLASS',
        'Manufacturer' => [
            'Name' => 'Anvil Aerospace',
            'Code' => 'ANV',
        ],
    ];

    $result = $this->service->findMatch($payload);

    expect($result)->toBe($vehicle->id);
});

it('strips Teach\'s Special suffix and matches base model', function (): void {
    $drake = ShipMatrixManufacturer::query()->create([
        'cig_id' => 6,
        'name' => 'Drake Interplanetary',
        'name_short' => 'DRAK',
        'slug' => 'drake-interplanetary',
    ]);

    $vehicle = ShipMatrixVehicle::query()->create([
        'cig_id' => 13,
        'name' => 'Vulture',
        'slug' => 'vulture',
        'manufacturer_id' => $drake->id,
        'production_status_id' => $this->productionStatus->id,
        'production_note_id' => $this->productionNote->id,
        'size_id' => $this->size->id,
        'type_id' => $this->type->id,
        'chassis_id' => 13,
    ]);

    $payload = [
        'UUID' => 'test-uuid',
        'Name' => 'Drake Vulture Teach\'s Special', // Has Teach's Special suffix
        'ClassName' => 'TEST_CLASS',
        'Manufacturer' => [
            'Name' => 'Drake Interplanetary',
            'Code' => 'DRAK',
        ],
    ];

    $result = $this->service->findMatch($payload);

    expect($result)->toBe($vehicle->id);
});
