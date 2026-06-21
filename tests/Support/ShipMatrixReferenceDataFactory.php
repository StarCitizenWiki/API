<?php

declare(strict_types=1);

use App\Models\StarCitizen\ShipMatrix\Manufacturer as ShipMatrixManufacturer;
use App\Models\StarCitizen\ShipMatrix\ProductionNote;
use App\Models\StarCitizen\ShipMatrix\ProductionStatus;
use App\Models\StarCitizen\ShipMatrix\Vehicle\Size as ShipSize;
use App\Models\StarCitizen\ShipMatrix\Vehicle\Type as ShipType;

if (! function_exists('createShipMatrixReferenceData')) {
    /**
     * Seeds the five ShipMatrix reference rows (ProductionStatus, ProductionNote,
     * Size, Type, Manufacturer) shared by BackfillShipmatrixIdsTest and
     * VehicleMatchingServiceTest. Binds them on the test case under canonical
     * property names so individual tests can reference $this->productionStatus etc.
     */
    function createShipMatrixReferenceData(): void
    {
        $test = test();

        $test->productionStatus = ProductionStatus::query()->create([
            'name' => 'In Production',
            'slug' => 'in-production',
        ]);

        $test->productionNote = ProductionNote::query()->create([
            'translation' => ['en' => 'None'],
        ]);

        $test->size = ShipSize::query()->create([
            'slug' => 'small',
            'size' => 'Small',
        ]);

        $test->type = ShipType::query()->create([
            'slug' => 'fighter',
            'type' => 'Fighter',
        ]);

        $test->shipMatrixManufacturer = ShipMatrixManufacturer::query()->create([
            'cig_id' => 1,
            'name' => 'Anvil Aerospace',
            'name_short' => 'ANV',
            'slug' => 'anvil-aerospace',
        ]);
    }
}