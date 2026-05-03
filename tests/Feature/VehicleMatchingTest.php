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
});

it('falls back to class-name parsing when the vehicle payload has no name', function (): void {
    $productionStatus = ProductionStatus::query()->create([
        'name' => 'In Production',
        'slug' => 'in-production',
    ]);

    $productionNote = ProductionNote::query()->create([
        'translation' => ['en' => 'None'],
    ]);

    $size = ShipSize::query()->create([
        'slug' => 'large',
        'size' => 'Large',
    ]);

    $type = ShipType::query()->create([
        'slug' => 'fighter',
        'type' => 'Fighter',
    ]);

    $manufacturer = ShipMatrixManufacturer::query()->create([
        'cig_id' => 1,
        'name' => 'Aegis Dynamics',
        'name_short' => 'AEGS',
        'slug' => 'aegis-dynamics',
    ]);

    $vehicle = ShipMatrixVehicle::query()->create([
        'cig_id' => 42,
        'name' => 'Retaliator Bomber',
        'slug' => 'retaliator-bomber',
        'manufacturer_id' => $manufacturer->id,
        'production_status_id' => $productionStatus->id,
        'production_note_id' => $productionNote->id,
        'size_id' => $size->id,
        'type_id' => $type->id,
        'chassis_id' => 42,
    ]);

    $result = app(VehicleMatchingService::class)->findMatch([
        'UUID' => fake()->uuid(),
        'ClassName' => 'AEGS_Retaliator_Bomber',
        'Manufacturer' => [
            'Name' => 'Aegis Dynamics',
            'Code' => 'AEGS',
        ],
    ]);

    expect($result)->toBe($vehicle->id);
});

it('uses configured vehicle name overrides before matching', function (): void {
    $productionStatus = ProductionStatus::query()->create([
        'name' => 'In Production',
        'slug' => 'in-production',
    ]);

    $productionNote = ProductionNote::query()->create([
        'translation' => ['en' => 'None'],
    ]);

    $size = ShipSize::query()->create([
        'slug' => 'large',
        'size' => 'Large',
    ]);

    $type = ShipType::query()->create([
        'slug' => 'fighter',
        'type' => 'Fighter',
    ]);

    $manufacturer = ShipMatrixManufacturer::query()->create([
        'cig_id' => 1,
        'name' => 'Aegis Dynamics',
        'name_short' => 'AEGS',
        'slug' => 'aegis-dynamics',
    ]);

    $vehicle = ShipMatrixVehicle::query()->create([
        'cig_id' => 43,
        'name' => 'Retaliator Bomber',
        'slug' => 'retaliator-bomber',
        'manufacturer_id' => $manufacturer->id,
        'production_status_id' => $productionStatus->id,
        'production_note_id' => $productionNote->id,
        'size_id' => $size->id,
        'type_id' => $type->id,
        'chassis_id' => 43,
    ]);

    $originalOverrides = config('game.vehicle_name_overrides', []);

    try {
        config()->set('game.vehicle_name_overrides', [
            ...$originalOverrides,
            'Aegis Retaliator' => 'Retaliator Bomber',
        ]);

        $result = app(VehicleMatchingService::class)->findMatch([
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
