<?php

namespace Tests\Feature\Jobs\Api\StarCitizen\Vehicle\Import;

use App\Jobs\StarCitizen\Vehicle\Import\ImportShipMatrix;
use App\Jobs\StarCitizen\Vehicle\Import\ImportVehicle;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

class ImportShipMatrixTest extends TestCase
{
    /**
     * @covers \App\Jobs\StarCitizen\Vehicle\Import\ImportShipMatrix::__construct
     * @covers \App\Jobs\StarCitizen\Vehicle\Import\ImportShipMatrix::handle
     */
    public function testMissingFile(): void
    {
        Storage::fake('vehicles');

        /** @var ImportShipMatrix $job */
        $job = $this->partialMock(
            ImportShipMatrix::class,
            function ($mock) {
                $mock->shouldReceive('fail')->once();
            }
        );

        $job->handle();
    }

    /**
     * @covers \App\Jobs\StarCitizen\Vehicle\Import\ImportShipMatrix::__construct
     * @covers \App\Jobs\StarCitizen\Vehicle\Import\ImportShipMatrix::handle
     */
    public function testInvalidFile(): void
    {
        Storage::fake('vehicles');
        $fileName = sprintf('%d/%s', now()->year, 'vehicle.json');

        Storage::persistentFake('vehicles')->put(
            $fileName,
            'INVALID]'
        );

        $job = Mockery::mock(ImportShipMatrix::class, [$fileName])->makePartial();
        $job->shouldReceive('delete')->once();

        $job->handle();
    }

    /**
     * @covers \App\Jobs\StarCitizen\Vehicle\Import\ImportShipMatrix::__construct
     * @covers \App\Jobs\StarCitizen\Vehicle\Import\ImportShipMatrix::handle
     */
    public function testImport(): void
    {
        Storage::fake('vehicles');
        $fileName = sprintf('%d/%s', now()->year, 'vehicle.json');

        Storage::persistentFake('vehicles')->put(
            $fileName,
            '[{}, {}]'
        );

        $job = new ImportShipMatrix($fileName);

        Bus::fake();

        $job->handle();

        Bus::assertDispatchedTimes(ImportVehicle::class, 2);
    }
}
