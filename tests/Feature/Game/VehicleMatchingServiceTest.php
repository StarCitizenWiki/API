<?php

declare(strict_types=1);

use App\Models\StarCitizen\ShipMatrix\Manufacturer as ShipMatrixManufacturer;
use App\Models\StarCitizen\ShipMatrix\ProductionNote;
use App\Models\StarCitizen\ShipMatrix\ProductionStatus;
use App\Models\StarCitizen\ShipMatrix\Vehicle\Size as ShipSize;
use App\Models\StarCitizen\ShipMatrix\Vehicle\Type as ShipType;
use App\Models\StarCitizen\ShipMatrix\Vehicle\Vehicle as ShipMatrixVehicle;
use App\Services\Game\VehicleMatchingService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    VehicleMatchingService::resetState();

    // Create required reference data for ship matrix vehicles
    $this->productionStatus = ProductionStatus::query()->create([
        'name' => 'In Production',
        'slug' => 'in-production',
    ]);

    $this->productionNote = ProductionNote::query()->create([
        'translation' => ['en' => 'None'],
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

it('finds matches for default manufacturer name permutations', function (
    int $cigId,
    int $chassisId,
    string $vehicleName,
    string $vehicleSlug,
    string $payloadName
): void {
    $vehicle = ShipMatrixVehicle::query()->create([
        'cig_id' => $cigId,
        'name' => $vehicleName,
        'slug' => $vehicleSlug,
        'manufacturer_id' => $this->manufacturer->id,
        'production_status_id' => $this->productionStatus->id,
        'production_note_id' => $this->productionNote->id,
        'size_id' => $this->size->id,
        'type_id' => $this->type->id,
        'chassis_id' => $chassisId,
    ]);

    $payload = [
        'UUID' => 'test-uuid',
        'Name' => $payloadName,
        'ClassName' => 'TEST_CLASS',
        'Manufacturer' => [
            'Name' => 'Anvil Aerospace',
            'Code' => 'ANV',
        ],
    ];

    $result = $this->service->findMatch($payload);

    expect($result)->toBe($vehicle->id);
})->with([
    'exact name' => [1, 1, 'Hornet F7C', 'hornet-f7c', 'Hornet F7C'],
    'manufacturer prefix stripped' => [2, 2, 'F7C Hornet', 'f7c-hornet', 'Anvil F7C Hornet'],
    'fuzzy matching within levenshtein threshold' => [4, 4, 'Gladius', 'gladius', 'Gladios'],
    'slug matching when name uses underscores' => [7, 7, 'Cutlass Black', 'cutlass-black', 'Cutlass_Black'],
    'wikelo suffix stripping' => [8, 8, 'Sabre Firebird', 'sabre-firebird', 'Anvil Sabre Firebird Wikelo War Special'],
    'pyam exec suffix stripping' => [9, 9, 'F8C Lightning', 'f8c-lightning', 'F8C Lightning PYAM Exec'],
]);

it('uses config overrides for matching', function (): void {
    $originalOverrides = config('game.vehicle_name_overrides', []);

    try {
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
    } finally {
        config(['game.vehicle_name_overrides' => $originalOverrides]);
    }
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

it('matches manufacturer-specific name permutations', function (
    int $manufacturerCigId,
    string $manufacturerName,
    string $manufacturerCode,
    string $manufacturerSlug,
    int $vehicleCigId,
    int $chassisId,
    string $vehicleName,
    string $vehicleSlug,
    string $payloadName
): void {
    $manufacturer = ShipMatrixManufacturer::query()->create([
        'cig_id' => $manufacturerCigId,
        'name' => $manufacturerName,
        'name_short' => $manufacturerCode,
        'slug' => $manufacturerSlug,
    ]);

    $vehicle = ShipMatrixVehicle::query()->create([
        'cig_id' => $vehicleCigId,
        'name' => $vehicleName,
        'slug' => $vehicleSlug,
        'manufacturer_id' => $manufacturer->id,
        'production_status_id' => $this->productionStatus->id,
        'production_note_id' => $this->productionNote->id,
        'size_id' => $this->size->id,
        'type_id' => $this->type->id,
        'chassis_id' => $chassisId,
    ]);

    $payload = [
        'UUID' => 'test-uuid',
        'Name' => $payloadName,
        'ClassName' => 'TEST_CLASS',
        'Manufacturer' => [
            'Name' => $manufacturerName,
            'Code' => $manufacturerCode,
        ],
    ];

    $result = $this->service->findMatch($payload);

    expect($result)->toBe($vehicle->id);
})->with([
    'special abbreviation rsi' => [3, 'Roberts Space Industries', 'RSI', 'roberts-space-industries', 6, 6, 'Aurora', 'aurora', 'RSI Aurora'],
    'best in show year reordering' => [4, 'Aegis Dynamics', 'AEGS', 'aegis-dynamics', 10, 10, 'Hammerhead Best In Show Edition 2949', 'hammerhead-best-in-show-edition-2949', 'Aegis Hammerhead 2949 Best In Show Edition'],
    'color variant suffix stripping' => [5, 'Argo Astronautics', 'ARGO', 'argo-astronautics', 11, 11, 'ATLS GEO', 'atls-geo', 'ATLS Snowland Color'],
    'teach special suffix stripping' => [6, 'Drake Interplanetary', 'DRAK', 'drake-interplanetary', 13, 13, 'Vulture', 'vulture', "Drake Vulture Teach's Special"],
]);

it('uses config override for hornet heartseeker variant', function (): void {
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
