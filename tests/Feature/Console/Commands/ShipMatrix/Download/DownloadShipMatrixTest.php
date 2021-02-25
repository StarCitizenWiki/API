<?php

declare(strict_types=1);

namespace Tests\Feature\Console\Commands\ShipMatrix\Download;

use App\Jobs\StarCitizen\Vehicle\CheckShipMatrixStructure;
use App\Jobs\StarCitizen\Vehicle\DownloadShipMatrix;
use App\Jobs\StarCitizen\Vehicle\Import\ImportShipMatrix;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class DownloadShipMatrixTest extends TestCase
{
    /**
     * Test without import
     *
     * @return void
     */
    public function testHandleWithoutImport(): void
    {
        Bus::fake();

        $this->artisan('ship-matrix:download')
            ->expectsOutput('Dispatching Ship Matrix Download Job')
            ->assertExitCode(0);

        Bus::assertDispatched(DownloadShipMatrix::class);
    }

    /**
     * Test with import
     *
     * @return void
     */
    public function testHandleWithImport(): void
    {
        Queue::fake();

        $this->artisan('ship-matrix:download --import')
            ->expectsOutput('Downloading Ship Matrix and starting import')
            ->assertExitCode(0);

        Queue::assertPushedWithChain(
            DownloadShipMatrix::class,
            [
                CheckShipMatrixStructure::class,
                ImportShipMatrix::class,
            ]
        );
    }
}
