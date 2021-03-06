<?php

namespace Tests\Feature\Jobs\Api\StarCitizen\Vehicle\Import;

use App\Jobs\StarCitizen\Vehicle\Import\ImportVehicle;
use App\Models\StarCitizen\Vehicle\Focus\Focus;
use App\Models\StarCitizen\Vehicle\Ship\Ship;
use App\Models\StarCitizen\Vehicle\Size\Size;
use App\Models\StarCitizen\Vehicle\Type\Type;
use Database\Seeders\StarCitizen\ProductionNoteTableSeeder;
use Database\Seeders\StarCitizen\ProductionStatusTableSeeder;
use Database\Seeders\StarCitizen\Vehicle\SizeTableSeeder;
use Database\Seeders\StarCitizen\Vehicle\TypeTableSeeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ImportVehicleTest extends TestCase
{
    /**
     * @covers \App\Jobs\StarCitizen\Vehicle\Import\ImportVehicle::handle
     * @covers \App\Jobs\StarCitizen\Vehicle\Import\ImportVehicle::getData
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
        $data = File::get(storage_path('framework/testing/shipmatrix/aurora_es.json'));
        $data = collect(json_decode($data, true));

        $job = new ImportVehicle($data);
        $job->handle();

        self::assertEquals(1, Ship::query()->count());
        self::assertEquals(2, Focus::query()->count()); // Starter / Pathfinder
        self::assertEquals(2, Size::query()->count());
        self::assertEquals(2, Type::query()->count());
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->createSystemLanguages();
        Artisan::call(
            'db:seed',
            [
                '--class' => ProductionNoteTableSeeder::class,
            ]
        );
        Artisan::call(
            'db:seed',
            [
                '--class' => ProductionStatusTableSeeder::class,
            ]
        );
        Artisan::call(
            'db:seed',
            [
                '--class' => SizeTableSeeder::class,
            ]
        );
        Artisan::call(
            'db:seed',
            [
                '--class' => TypeTableSeeder::class,
            ]
        );
    }
}
