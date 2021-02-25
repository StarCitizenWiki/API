<?php

declare(strict_types=1);

namespace Tests\Feature\Services\Parser\ShipMatrix;

use App\Jobs\StarCitizen\Vehicle\Import\ImportVehicle;
use App\Models\StarCitizen\Manufacturer\Manufacturer;
use App\Models\StarCitizen\ProductionNote\ProductionNote;
use App\Models\StarCitizen\ProductionStatus\ProductionStatus;
use App\Models\StarCitizen\Vehicle\Focus\Focus;
use App\Models\StarCitizen\Vehicle\Ship\Ship;
use App\Models\StarCitizen\Vehicle\Size\Size;
use App\Models\StarCitizen\Vehicle\Type\Type;
use App\Models\StarCitizen\Vehicle\Vehicle\VehicleComponent;
use Database\Seeders\StarCitizen\ProductionNoteTableSeeder;
use Database\Seeders\StarCitizen\ProductionStatusTableSeeder;
use Database\Seeders\StarCitizen\Vehicle\SizeTableSeeder;
use Database\Seeders\StarCitizen\Vehicle\TypeTableSeeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ParseVehicleTest extends TestCase
{
    /**
     * @covers \App\Jobs\StarCitizen\Vehicle\Import\ImportVehicle
     * @covers \App\Services\Parser\ShipMatrix\Manufacturer
     * @covers \App\Services\Parser\ShipMatrix\ProductionNote
     * @covers \App\Services\Parser\ShipMatrix\ProductionStatus
     * @covers \App\Services\Parser\ShipMatrix\Vehicle\Type
     * @covers \App\Services\Parser\ShipMatrix\Vehicle\Focus
     * @covers \App\Services\Parser\ShipMatrix\Vehicle\Size
     * @covers \App\Services\Parser\ShipMatrix\Component
     * @covers \App\Services\Parser\ShipMatrix\AbstractBaseElement
     */
    public function testParsing()
    {
        $data = File::get(storage_path('framework/testing/shipmatrix/aurora_es.json'));
        $data = collect(json_decode($data, true));

        $parser = new ImportVehicle($data);
        $parser->handle();

        $this->assertDatabaseHas(
            'vehicles',
            [
                'name' => 'Aurora ES',
            ]
        )->assertDatabaseHas(
            'manufacturers',
            [
                'name_short' => 'RSI',
            ]
        );

        self::assertCount(1, Ship::all());
        self::assertCount(1, Manufacturer::all());
        self::assertCount(2, ProductionStatus::all());
        self::assertCount(1, ProductionNote::all());
        self::assertCount(2, Focus::all());
        self::assertCount(2, Type::all());
        self::assertCount(2, Size::all());
        self::assertCount(15, VehicleComponent::all());
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
