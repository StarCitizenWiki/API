<?php

declare(strict_types=1);

namespace Tests\Feature\Console\Commands\CommLink\Import;

use App\Jobs\Rsi\CommLink\Import\ImportCommLinks;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class ImportCommLinksTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testHandleNoOffset(): void
    {
        Bus::fake();

        $this->artisan('comm-links:import-all')
            ->expectsOutput('Dispatching Comm-Link Import')
            ->expectsOutput('Including all Comm-Links')
            ->assertExitCode(0);

        Bus::assertDispatched(ImportCommLinks::class);
    }

    public function testHandleOffset(): void
    {
        Bus::fake();

        $this->artisan('comm-links:import-all 10')
            ->expectsOutput('Dispatching Comm-Link Import')
            ->expectsOutput("Including Comm-Links that were created in the last '10' minutes")
            ->assertExitCode(0);

        Bus::assertDispatched(ImportCommLinks::class);
    }

    public function testHandleOffsetGreaterThan0(): void
    {
        Bus::fake();

        $this->artisan('comm-links:import-all 12700')
            ->expectsOutput('Dispatching Comm-Link Import')
            ->expectsOutput("Including Comm-Links that were created in the last '12700' minutes")
            ->assertExitCode(0);

        Bus::assertDispatched(ImportCommLinks::class);
    }
}
