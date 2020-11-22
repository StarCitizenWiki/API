<?php

declare(strict_types=1);

namespace Tests\Feature\Console\Commands\ShipMatrix\Import;

use App\Jobs\Api\StarCitizen\Vehicle\Import\ImportShipMatrix;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class ImportShipMatrixTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testHandle(): void
    {
        Bus::fake();

        $this->artisan('ship-matrix:import')
            ->expectsOutput('Dispatching Ship Matrix Parsing Job')
            ->assertExitCode(0);

        Bus::assertDispatched(ImportShipMatrix::class);
    }
}
