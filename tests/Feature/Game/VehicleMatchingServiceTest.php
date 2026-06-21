<?php

declare(strict_types=1);

use App\Models\StarCitizen\ShipMatrix\Manufacturer as ShipMatrixManufacturer;
use App\Models\StarCitizen\ShipMatrix\Vehicle\Vehicle as ShipMatrixVehicle;
use App\Services\Game\VehicleMatchingService;

beforeEach(function (): void {
    VehicleMatchingService::resetState();

    createShipMatrixReferenceData();

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
        'manufacturer_id' => $this->shipMatrixManufacturer->id,
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
            'manufacturer_id' => $this->shipMatrixManufacturer->id,
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

it('does not fuzzy match across manufacturers when the manufacturer is unknown', function (): void {
    // "Mule" exists in the ship matrix (Drake), but the game vehicle is an NPC
    // ship from a manufacturer that is not in the ship matrix (e.g. Vanduul).
    // "Mauler" is within Levenshtein distance 2 of "Mule", but must NOT match.
    ShipMatrixVehicle::query()->create([
        'cig_id' => 99,
        'name' => 'Mule',
        'slug' => 'mule',
        'manufacturer_id' => $this->shipMatrixManufacturer->id,
        'production_status_id' => $this->productionStatus->id,
        'production_note_id' => $this->productionNote->id,
        'size_id' => $this->size->id,
        'type_id' => $this->type->id,
        'chassis_id' => 99,
    ]);

    $payload = [
        'UUID' => '5d2bf0b6-d9be-4738-8717-0f455c461d8a',
        'Name' => 'Vanduul Mauler Destroyer',
        'ClassName' => 'VNCL_Mauler',
        'Manufacturer' => [
            'Name' => 'Vanduul',
            'Code' => 'VNCL',
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

it('lets an exact match on one candidate win over a substring match on another', function (): void {
    // Guards the tiered design: "MOLE" must exact-match "MOLE" (slug "mole"),
    // not let the candidate "Argo MOLE" substring-match "Argo Mole Carbon Edition".
    $base = ShipMatrixVehicle::query()->create([
        'cig_id' => 60,
        'name' => 'MOLE',
        'slug' => 'mole',
        'manufacturer_id' => $this->shipMatrixManufacturer->id,
        'production_status_id' => $this->productionStatus->id,
        'production_note_id' => $this->productionNote->id,
        'size_id' => $this->size->id,
        'type_id' => $this->type->id,
        'chassis_id' => 60,
    ]);
    ShipMatrixVehicle::query()->create([
        'cig_id' => 61,
        'name' => 'Argo Mole Carbon Edition',
        'slug' => 'argo-mole-carbon-edition',
        'manufacturer_id' => $this->shipMatrixManufacturer->id,
        'production_status_id' => $this->productionStatus->id,
        'production_note_id' => $this->productionNote->id,
        'size_id' => $this->size->id,
        'type_id' => $this->type->id,
        'chassis_id' => 61,
    ]);

    $result = $this->service->findMatch([
        'UUID' => fake()->uuid(),
        'Name' => 'Argo MOLE',
        'ClassName' => 'ARGO_MOLE',
        'Manufacturer' => ['Name' => $this->shipMatrixManufacturer->name, 'Code' => 'ANV'],
    ]);

    expect($result)->toBe($base->id);
});

it('matches a base ship whose name is a prefix of a ship matrix variant', function (): void {
    // Exercises the substring (contains) tier: "Caterpillar Pirate" is not an
    // exact ship matrix name, but it is a prefix of "Caterpillar Pirate Edition".
    $vehicle = ShipMatrixVehicle::query()->create([
        'cig_id' => 62,
        'name' => 'Caterpillar Pirate Edition',
        'slug' => 'caterpillar-pirate-edition',
        'manufacturer_id' => $this->shipMatrixManufacturer->id,
        'production_status_id' => $this->productionStatus->id,
        'production_note_id' => $this->productionNote->id,
        'size_id' => $this->size->id,
        'type_id' => $this->type->id,
        'chassis_id' => 62,
    ]);

    $result = $this->service->findMatch([
        'UUID' => fake()->uuid(),
        'Name' => 'Anvil Caterpillar Pirate',
        'ClassName' => 'ANVL_Caterpillar_Pirate',
        'Manufacturer' => ['Name' => 'Anvil Aerospace', 'Code' => 'ANV'],
    ]);

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
]);

it('uses config override for hornet heartseeker variant', function (): void {
    config(['game.vehicle_name_overrides' => [
        'Anvil F7C-M Hornet Heartseeker Mk I' => 'F7C-M Super Hornet Heartseeker Mk I',
    ]]);

    $vehicle = ShipMatrixVehicle::query()->create([
        'cig_id' => 12,
        'name' => 'F7C-M Super Hornet Heartseeker Mk I',
        'slug' => 'f7c-m-super-hornet-heartseeker-mk-i',
        'manufacturer_id' => $this->shipMatrixManufacturer->id,
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

it('does not match in-game event editions onto their base vehicle', function (string $payloadName): void {
    // Wikelo/Teach's Special and PYAM Exec are obtainable in-game event ships
    // with no own ship matrix entry. They must NOT collapse onto the base
    // vehicle ("Vulture") and inherit its pledge MSRP.
    ShipMatrixVehicle::query()->create([
        'cig_id' => 70,
        'name' => 'Vulture',
        'slug' => 'vulture',
        'manufacturer_id' => $this->shipMatrixManufacturer->id,
        'production_status_id' => $this->productionStatus->id,
        'production_note_id' => $this->productionNote->id,
        'size_id' => $this->size->id,
        'type_id' => $this->type->id,
        'chassis_id' => 70,
    ]);

    $result = $this->service->findMatch([
        'UUID' => fake()->uuid(),
        'Name' => $payloadName,
        'ClassName' => 'DRAK_Vulture_Collector',
        'Manufacturer' => [
            'Name' => 'Drake Interplanetary',
            'Code' => 'DRAK',
        ],
    ]);

    expect($result)->toBeNull();
})->with([
    'wikelo war special' => 'Drake Vulture Wikelo War Special',
    'wikelo work special' => 'Drake Vulture Wikelo Work Special',
    'wikelo special' => 'Drake Vulture Wikelo Special',
    "teach's special" => "Drake Vulture Teach's Special",
    'pyam exec' => 'Drake Vulture PYAM Exec',
]);

it('does not strip the IKTI special-edition suffix onto a base vehicle', function (): void {
    // IKTI variants are distinct in-game special editions with no own ship
    // matrix entry. Stripping the suffix would collapse them onto the base
    // vehicle (e.g. "ATLS IKTI" -> "ATLS") and wrongly inherit its MSRP.
    ShipMatrixVehicle::query()->create([
        'cig_id' => 80,
        'name' => 'ATLS',
        'slug' => 'atls',
        'manufacturer_id' => $this->shipMatrixManufacturer->id,
        'production_status_id' => $this->productionStatus->id,
        'production_note_id' => $this->productionNote->id,
        'size_id' => $this->size->id,
        'type_id' => $this->type->id,
        'chassis_id' => 80,
    ]);

    $payload = [
        'UUID' => 'test-uuid',
        'Name' => 'Argo ATLS IKTI',
        'ClassName' => 'ARGO_ATLS_IKTI',
        'Manufacturer' => [
            'Name' => 'Argo Astronautics',
            'Code' => 'ARGO',
        ],
    ];

    $result = $this->service->findMatch($payload);

    expect($result)->toBeNull();
});

it('falls back to class-name parsing when the vehicle payload has no name', function (): void {
    $vehicle = ShipMatrixVehicle::query()->create([
        'cig_id' => 42,
        'name' => 'Retaliator Bomber',
        'slug' => 'retaliator-bomber',
        'manufacturer_id' => $this->shipMatrixManufacturer->id,
        'production_status_id' => $this->productionStatus->id,
        'production_note_id' => $this->productionNote->id,
        'size_id' => $this->size->id,
        'type_id' => $this->type->id,
        'chassis_id' => 42,
    ]);

    $result = $this->service->findMatch([
        'UUID' => fake()->uuid(),
        'ClassName' => 'ANV_Retaliator_Bomber',
        'Manufacturer' => [
            'Name' => 'Anvil Aerospace',
            'Code' => 'ANV',
        ],
    ]);

    expect($result)->toBe($vehicle->id);
});

it('uses configured vehicle name overrides before matching', function (): void {
    $manufacturer = ShipMatrixManufacturer::query()->create([
        'cig_id' => 50,
        'name' => 'Aegis Dynamics',
        'name_short' => 'AEGS',
        'slug' => 'aegis-dynamics',
    ]);

    $vehicle = ShipMatrixVehicle::query()->create([
        'cig_id' => 43,
        'name' => 'Retaliator Bomber',
        'slug' => 'retaliator-bomber',
        'manufacturer_id' => $manufacturer->id,
        'production_status_id' => $this->productionStatus->id,
        'production_note_id' => $this->productionNote->id,
        'size_id' => $this->size->id,
        'type_id' => $this->type->id,
        'chassis_id' => 43,
    ]);

    $originalOverrides = config('game.vehicle_name_overrides', []);

    try {
        config()->set('game.vehicle_name_overrides', [
            ...$originalOverrides,
            'Aegis Retaliator' => 'Retaliator Bomber',
        ]);

        $result = $this->service->findMatch([
            'UUID' => fake()->uuid(),
            'Name' => 'Aegis Retaliator',
            'ClassName' => 'AEGS_Retaliator',
            'Manufacturer' => [
                'Name' => 'Aegis Dynamics',
                'Code' => 'AEGS',
            ],
        ]);

        expect($result)->toBe($vehicle->id);
    } finally {
        config()->set('game.vehicle_name_overrides', $originalOverrides);
    }
});
