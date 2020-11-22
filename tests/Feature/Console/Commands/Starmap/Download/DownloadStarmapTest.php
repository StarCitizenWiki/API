<?php

declare(strict_types=1);

namespace Tests\Feature\Console\Commands\Starmap\Download;

use App\Jobs\Api\StarCitizen\Starmap\Download\DownloadStarmap;
use App\Jobs\Api\StarCitizen\Starmap\Import\ImportStarmap;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class DownloadStarmapTest extends TestCase
{
    /**
     * Download starmap
     *
     * @return void
     *
     * @covers \App\Console\Commands\Starmap\Download\DownloadStarmap
     */
    public function testDownload(): void
    {
        Bus::fake(
            [
                DownloadStarmap::class,
            ]
        );

        $this->artisan('starmap:download')
            ->expectsOutput('Dispatching Starmap Download')
            ->assertExitCode(0);

        Bus::assertDispatched(DownloadStarmap::class);
    }

    /**
     * Download starmap and import
     *
     * @return void
     *
     * @covers \App\Console\Commands\Starmap\Download\DownloadStarmap
     */
    public function testDownloadImport(): void
    {
        Bus::fake(
            [
                DownloadStarmap::class,
                ImportStarmap::class,
            ]
        );

        $this->artisan('starmap:download --import')
            ->expectsOutput('Dispatching Starmap Download')
            ->expectsOutput('Starting Import')
            ->assertExitCode(0);

        Bus::assertDispatched(DownloadStarmap::class);
        Bus::assertDispatched(ImportStarmap::class);
    }
}
