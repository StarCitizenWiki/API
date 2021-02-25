<?php

namespace Tests\Feature\Console\Commands\Starmap\Import;

use App\Jobs\StarCitizen\Starmap\Import\ImportStarmap;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class ImportStarmapTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     *
     * @covers \App\Console\Commands\Starmap\Import\ImportStarmap
     */
    public function testHandle(): void
    {
        Bus::fake(
            [
                ImportStarmap::class,
            ]
        );

        $this->artisan('starmap:import')
            ->expectsOutput('Importing starmap')
            ->assertExitCode(0);

        Bus::assertDispatched(ImportStarmap::class);
    }
}
