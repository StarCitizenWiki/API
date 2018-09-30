<?php declare(strict_types = 1);

namespace App\Console;

use App\Console\Commands\CommLink\Download\ReDownloadCommLinks;
use App\Console\Commands\CommLink\Import\ImportMissingCommLinks;
use App\Console\Commands\ShipMatrix\Import\ImportShipMatrix;
use App\Console\Commands\Stat\Import\ImportStats;
use App\Events\Rsi\CommLink\CommLinksChanged as CommLinksChangedEvent;
use App\Events\Rsi\CommLink\NewCommLinksDownloaded;
use App\Jobs\Rsi\CommLink\Download\Image\DownloadCommLinkImages;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

/**
 * Class Kernel
 */
class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\ShipMatrix\Import\ImportShipMatrix::class,

        \App\Console\Commands\Stat\Import\ImportStats::class,

        \App\Console\Commands\CommLink\Import\ImportCommLinks::class,
        \App\Console\Commands\CommLink\Import\ImportMissingCommLinks::class,

        \App\Console\Commands\CommLink\Download\ReDownloadCommLinks::class,
    ];
    /**
     * @var \Illuminate\Console\Scheduling\Schedule
     */
    private $schedule;

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     *
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $this->schedule = $schedule;

        $this->scheduleStatJobs();
        $this->scheduleShipMatrixJobs();
        $this->scheduleCommLinkJobs();
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }

    /**
     * Stat related Jobs
     */
    private function scheduleStatJobs()
    {
        $this->schedule->command(ImportStats::class, ['--download'])->dailyAt('20:00');
    }

    /**
     * Comm Link related Jobs
     */
    private function scheduleCommLinkJobs()
    {
        /** Check for new Comm Links */
        $this->schedule->command(ImportMissingCommLinks::class)->hourly()->after(
            function () {
                $this->events->dispatch(new NewCommLinksDownloaded());
            }
        );

        /** Re-Download all Comm Links monthly */
        $this->schedule->command(ReDownloadCommLinks::class)->monthly()->after(
            function () {
                $this->events->dispatch(new CommLinksChangedEvent());
            }
        );

        /** Download Comm Link Images */
        $this->schedule->job(DownloadCommLinkImages::class)->daily()->withoutOverlapping();
    }

    /**
     * Ship Matrix related Jobs
     */
    private function scheduleShipMatrixJobs()
    {
        $this->schedule->command(ImportShipMatrix::class, ['--download'])->twiceDaily();
    }
}
