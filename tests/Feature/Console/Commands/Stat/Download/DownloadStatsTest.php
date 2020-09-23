<?php

declare(strict_types=1);

namespace Tests\Feature\Console\Commands\Stat\Download;

use App\Jobs\Api\StarCitizen\Stat\DownloadStats as DownloadStatsJob;
use App\Jobs\Api\StarCitizen\Stat\Parser\ParseStat;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class DownloadStatsTest extends TestCase
{
    /**
     * Test without import
     *
     * @return void
     */
    public function testHandleWithoutImport(): void
    {
        Bus::fake();

        $this->artisan('stats:download')
            ->expectsOutput('Starting funding statistics download')
            ->assertExitCode(0);

        Bus::assertDispatched(DownloadStatsJob::class);
    }

    /**
     * Test with import
     *
     * @return void
     */
    public function testHandleWithImport(): void
    {
        Queue::fake();

        $this->artisan('stats:download --import')
            ->expectsOutput('Downloading funding statistics and starting import')
            ->assertExitCode(0);

        Queue::assertPushedWithChain(
            DownloadStatsJob::class,
            [
                ParseStat::class,
            ]
        );
    }
}
