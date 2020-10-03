<?php

declare(strict_types=1);

namespace Tests\Feature\Console\Commands\CommLink\Import;

use App\Jobs\Rsi\CommLink\Parser\ParseCommLinkDownload;
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
            ->expectsOutput('Starting at Comm-Link ID 12663')
            ->assertExitCode(0);

        Bus::assertDispatched(ParseCommLinkDownload::class);
    }

    public function testHandleOffset(): void
    {
        Bus::fake();

        $this->artisan('comm-links:import-all 10')
            ->expectsOutput('Dispatching Comm-Link Import')
            ->expectsOutput('Starting at Comm-Link ID 12673')
            ->assertExitCode(0);

        Bus::assertDispatched(ParseCommLinkDownload::class);
    }

    public function testHandleOffsetGreaterThan0(): void
    {
        Bus::fake();

        $this->artisan('comm-links:import-all 12700')
            ->expectsOutput('Dispatching Comm-Link Import')
            ->expectsOutput('Starting at Comm-Link ID 12700')
            ->assertExitCode(0);

        Bus::assertDispatched(ParseCommLinkDownload::class);
    }
}
