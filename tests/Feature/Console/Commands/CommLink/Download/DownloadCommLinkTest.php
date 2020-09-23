<?php

declare(strict_types=1);

namespace Tests\Feature\Console\Commands\CommLink\Download;

use App\Jobs\Rsi\CommLink\Download\DownloadCommLink;
use App\Jobs\Rsi\CommLink\Image\CreateImageMetadata;
use App\Jobs\Rsi\CommLink\Parser\ParseCommLinkDownload;
use Illuminate\Support\Facades\Bus;
use Symfony\Component\Console\Exception\RuntimeException;
use Tests\TestCase;

class DownloadCommLinkTest extends TestCase
{
    /**
     * Download singular Comm-Link
     *
     * @return void
     */
    public function testDownloadOne(): void
    {
        Bus::fake(
            [
                DownloadCommLink::class,
            ]
        );

        $this->artisan('comm-links:download 12663')
            ->expectsOutput('Downloading specified Comm-Links')
            ->assertExitCode(0);

        Bus::assertDispatchedTimes(DownloadCommLink::class, 1);
    }

    /**
     * Download multiple Comm-Links
     *
     * @return void
     */
    public function testDownloadMultiple(): void
    {
        Bus::fake(
            [
                DownloadCommLink::class,
            ]
        );

        $this->artisan('comm-links:download 12663 12664 12665')
            ->expectsOutput('Downloading specified Comm-Links')
            ->assertExitCode(0);

        Bus::assertDispatchedTimes(DownloadCommLink::class, 3);
    }

    /**
     * Call command without id
     *
     * @return void
     */
    public function testDownloadNoIds(): void
    {
        $this->expectException(RuntimeException::class);

        $this->artisan('comm-links:download')
            ->expectsOutput('At least one Comm-Link ID needs to be specified')
            ->assertExitCode(1);
    }

    /**
     * Download singular Comm-Link and import
     *
     * @return void
     */
    public function testDownloadImport(): void
    {
        Bus::fake(
            [
                DownloadCommLink::class,
                ParseCommLinkDownload::class,
                CreateImageMetadata::class,
            ]
        );

        $this->artisan('comm-links:download 12663 --import')
            ->expectsOutput('Downloading specified Comm-Links')
            ->expectsOutput("\nImporting Comm-Links")
            ->assertExitCode(0);

        Bus::assertDispatched(DownloadCommLink::class);
        Bus::assertDispatched(ParseCommLinkDownload::class);
        Bus::assertDispatched(CreateImageMetadata::class);
    }
}
