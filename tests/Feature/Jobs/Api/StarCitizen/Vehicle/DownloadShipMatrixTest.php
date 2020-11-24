<?php

namespace Tests\Feature\Jobs\Api\StarCitizen\Vehicle;

use App\Jobs\Api\StarCitizen\Vehicle\DownloadShipMatrix;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Class DownloadShipMatrixTest
 * @runTestsInSeparateProcesses
 */
class DownloadShipMatrixTest extends TestCase
{
    /**
     * @covers \App\Jobs\Api\StarCitizen\Vehicle\DownloadShipMatrix::__construct
     * @covers \App\Jobs\Api\StarCitizen\Vehicle\DownloadShipMatrix::handle
     */
    public function testServerError(): void
    {
        Http::fake(
            [
                '*' => Http::response('', 503),
            ]
        );

        /** @var DownloadShipMatrix $job */
        $job = $this->partialMock(
            DownloadShipMatrix::class,
            function ($mock) {
                $mock->shouldReceive('fail')->once();
            }
        );

        $job->handle();
    }

    /**
     * @covers \App\Jobs\Api\StarCitizen\Vehicle\DownloadShipMatrix::__construct
     * @covers \App\Jobs\Api\StarCitizen\Vehicle\DownloadShipMatrix::handle
     * @covers \App\Jobs\Api\StarCitizen\Vehicle\DownloadShipMatrix::getPath
     * @covers \App\Jobs\Api\StarCitizen\Vehicle\DownloadShipMatrix::parseResponseBody
     */
    public function testInvalidData(): void
    {
        Http::fake(
            [
                '*' => Http::response('INVALID', 200),
            ]
        );

        /** @var DownloadShipMatrix $job */
        $job = $this->partialMock(
            DownloadShipMatrix::class,
            function ($mock) {
                $mock->shouldReceive('fail')->once();
            }
        );

        $job->handle();
    }

    /**
     * @covers \App\Jobs\Api\StarCitizen\Vehicle\DownloadShipMatrix::__construct
     * @covers \App\Jobs\Api\StarCitizen\Vehicle\DownloadShipMatrix::handle
     * @covers \App\Jobs\Api\StarCitizen\Vehicle\DownloadShipMatrix::getPath
     * @covers \App\Jobs\Api\StarCitizen\Vehicle\DownloadShipMatrix::parseResponseBody
     */
    public function testValidJsonData(): void
    {
        $dirName = now()->format('Y-m-d');
        $fileTimeStamp = now()->format('Y-m-d_H-i');
        $filename = "shipmatrix_{$fileTimeStamp}.json";

        Storage::persistentFake('vehicles')->delete("$dirName/$filename");

        Http::fake(
            [
                '*' => Http::response('{"success":1, "data": []}', 200),
            ]
        );

        Storage::persistentFake('vehicles');

        $job = new DownloadShipMatrix();
        $job->handle();

        Storage::persistentFake('vehicles')->assertExists("$dirName/$filename");
    }
}
