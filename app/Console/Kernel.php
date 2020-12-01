<?php

declare(strict_types=1);

namespace App\Console;

use App\Console\Commands\CommLink\CommLinkSchedule;
use App\Console\Commands\CommLink\Download\DownloadCommLink;
use App\Console\Commands\CommLink\Download\DownloadCommLinks;
use App\Console\Commands\CommLink\Download\Image\DownloadCommLinkImages;
use App\Console\Commands\CommLink\Download\ReDownloadCommLinks;
use App\Console\Commands\CommLink\Image\CreateImageHashes;
use App\Console\Commands\CommLink\Image\CreateImageMetadata;
use App\Console\Commands\CommLink\Image\SyncImageIds;
use App\Console\Commands\CommLink\Import\ImportCommLink;
use App\Console\Commands\CommLink\Import\ImportCommLinks;
use App\Console\Commands\CommLink\Translate\TranslateCommLinks;
use App\Console\Commands\CommLink\Wiki\CreateCommLinkWikiPages;
use App\Console\Commands\ShipMatrix\Download\DownloadShipMatrix;
use App\Console\Commands\ShipMatrix\Import\ImportShipMatrix;
use App\Console\Commands\Starmap\Download\DownloadStarmap;
use App\Console\Commands\Starmap\Import\ImportStarmap;
use App\Console\Commands\Starmap\Translate\TranslateSystems;
use App\Console\Commands\Stat\Download\DownloadStats;
use App\Console\Commands\Stat\Import\ImportStats;
use App\Console\Commands\Transcript\ImportRelayTranscripts;
use App\Console\Commands\Transcript\TranslateTranscripts;
use App\Events\Rsi\CommLink\CommLinksChanged as CommLinksChangedEvent;
use App\Events\Rsi\CommLink\NewCommLinksDownloaded;
use App\Jobs\Wiki\CommLink\UpdateCommLinkProofReadStatus;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

/**
 * Class Kernel.
 */
class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        DownloadShipMatrix::class,
        ImportShipMatrix::class,

        DownloadStats::class,
        ImportStats::class,

        ImportCommLinks::class,
        ImportCommLink::class,
        CommLinkSchedule::class,

        DownloadCommLink::class,
        DownloadCommLinks::class,
        ReDownloadCommLinks::class,
        DownloadCommLinkImages::class,

        TranslateCommLinks::class,

        CreateCommLinkWikiPages::class,

        SyncImageIds::class,
        CreateImageHashes::class,
        CreateImageMetadata::class,

        ImportRelayTranscripts::class,
        TranslateTranscripts::class,

        DownloadStarmap::class,
        ImportStarmap::class,
        TranslateSystems::class,
    ];

    /**
     * @var Schedule
     */
    private Schedule $schedule;

    /**
     * Define the application's command schedule.
     *
     * @param Schedule $schedule
     *
     * @return void
     */
    protected function schedule(Schedule $schedule): void
    {
        $this->schedule = $schedule;

        $this->scheduleStatJobs();

        if (config('schedule.ship_matrix.enabled')) {
            $this->scheduleShipMatrixJobs();
        }

        if (config('schedule.comm_links.enabled')) {
            $this->scheduleCommLinkJobs();
        }

        if (config('schedule.starmap.enabled')) {
            $this->scheduleStarmapJobs();
        }
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands(): void
    {
        require base_path('routes/console.php');
    }

    /**
     * Stat related Jobs.
     */
    private function scheduleStatJobs(): void
    {
        $this->schedule
            ->command(DownloadStats::class, ['--import'])
            ->dailyAt('20:00');
    }

    /**
     * Comm-Link related Jobs.
     */
    private function scheduleCommLinkJobs(): void
    {
        /* Check for new Comm-Links */
        $this->schedule
            ->command(CommLinkSchedule::class)
            ->hourly()
            ->after(
                function () {
                    $this->events->dispatch(new NewCommLinksDownloaded());
                }
            );

        /* Re-Download all Comm-Links monthly */
        $this->schedule
            ->command(ReDownloadCommLinks::class)
            ->monthly()
            ->after(
                function () {
                    $this->events->dispatch(new CommLinksChangedEvent());
                }
            );

        /* Download Comm-Link Images */
        //$this->schedule->job(DownloadCommLinkImages::class)->daily()->withoutOverlapping();

        /* Update Proof Read Status */
        $this->schedule
            ->job(UpdateCommLinkProofReadStatus::class)
            ->daily()
            ->withoutOverlapping();
    }

    /**
     * Ship Matrix related Jobs.
     */
    private function scheduleShipMatrixJobs(): void
    {
        $hours = config('schedule.ship_matrix.at', []);
        // Ensure first and second key exists
        $hours = array_merge([1, 13], $hours);

        $this->schedule
            ->command(DownloadShipMatrix::class, ['--import'])
            ->twiceDaily(
                $hours[0],
                $hours[1],
            );
    }

    /**
     * Starmap download and import job
     */
    private function scheduleStarmapJobs(): void
    {
        $this->schedule
            ->command(DownloadStarmap::class, ['--import'])
            ->monthly();
    }
}
