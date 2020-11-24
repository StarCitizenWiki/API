<?php

namespace Tests\Feature\Jobs\Api\StarCitizen\Vehicle\Import;

use App\Jobs\Api\StarCitizen\Vehicle\Import\ImportVehicle;
use App\Models\Api\StarCitizen\Vehicle\Focus\Focus;
use App\Models\Api\StarCitizen\Vehicle\Ship\Ship;
use App\Models\Api\StarCitizen\Vehicle\Size\Size;
use App\Models\Api\StarCitizen\Vehicle\Type\Type;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ImportVehicleTest extends TestCase
{
    /**
     * @covers \App\Jobs\Api\StarCitizen\Vehicle\Import\ImportVehicle::handle
     * @covers \App\Jobs\Api\StarCitizen\Vehicle\Import\ImportVehicle::getData
     * @covers \App\Services\Parser\ShipMatrix\AbstractBaseElement
     * @covers \App\Services\Parser\ShipMatrix\Manufacturer
     * @covers \App\Services\Parser\ShipMatrix\ProductionNote
     * @covers \App\Services\Parser\ShipMatrix\ProductionStatus
     * @covers \App\Services\Parser\ShipMatrix\Component
     * @covers \App\Services\Parser\ShipMatrix\Vehicle\Type
     * @covers \App\Services\Parser\ShipMatrix\Vehicle\Size
     * @covers \App\Services\Parser\ShipMatrix\Vehicle\Focus
     */
    public function testImportAurora(): void
    {
        $data = File::get(resource_path('test_files/shipmatrix/aurora_es.json'));
        $data = collect(json_decode($data, true));

        $job = new ImportVehicle($data);
        $job->handle();

        self::assertEquals(1, Ship::query()->count());
        self::assertEquals(2, Focus::query()->count()); // Starter / Pathfinder
        self::assertEquals(1, Size::query()->count());
        self::assertEquals(1, Type::query()->count());
    }
}
