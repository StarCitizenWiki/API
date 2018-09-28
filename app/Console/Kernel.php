<?php declare(strict_types = 1);

namespace App\Console;

use App\Events\Rsi\CommLink\CommLinksChanged as CommLinksChangedEvent;
use App\Events\Rsi\CommLink\NewCommLinksDownloaded;
use App\Jobs\Api\StarCitizen\Stat\DownloadStats;
use App\Jobs\Api\StarCitizen\Stat\Parser\ParseStat;
use App\Jobs\Api\StarCitizen\Vehicle\DownloadShipMatrix;
use App\Jobs\Api\StarCitizen\Vehicle\Parser\ParseShipMatrixDownload;
use App\Jobs\Rsi\CommLink\DownloadMissingCommLinks;
use App\Jobs\Rsi\CommLink\Parser\ParseCommLinkDownload;
use App\Jobs\Rsi\CommLink\ReDownloadDbCommLinks;
use App\Models\Rsi\CommLink\CommLink;
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
        \App\Console\Commands\ShipMatrix\DownloadShipMatrix::class,
        \App\Console\Commands\ShipMatrix\ImportShipMatrix::class,

        \App\Console\Commands\Stat\DownloadStats::class,

        \App\Console\Commands\CommLink\ImportCommLinks::class,
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
        $this->schedule->job(new DownloadStats())->dailyAt('20:00')->then(
            function () {
                $this->schedule->job(new ParseStat());
            }
        );
    }

    /**
     * Comm Link related Jobs
     */
    private function scheduleCommLinkJobs()
    {
        /** Check for new Comm Links */
        $this->schedule->job(app(DownloadMissingCommLinks::class))->hourly()->withoutOverlapping()->then(
            function () {
                $missingOffset = optional(CommLink::orderByDesc('cig_id')->first())->cig_id ?? 0;
                if ($missingOffset > 0) {
                    $missingOffset++;
                }

                $this->schedule->job(new ParseCommLinkDownload($missingOffset));
            }
        )->then(
            function () {
                $this->events->dispatch(new NewCommLinksDownloaded());
            }
        );

        /** Re-Download all Comm Links monthly */
        $this->schedule->job(new ReDownloadDbCommLinks())->monthly()->then(
            function () {
                $this->schedule->job(new ParseCommLinkDownload());
            }
        )->then(
            function () {
                $this->events->dispatch(new CommLinksChangedEvent());
            }
        );
    }

    /**
     * Ship Matrix related Jobs
     */
    private function scheduleShipMatrixJobs()
    {
        $this->schedule->job(new DownloadShipMatrix())->hourly()->then(
            function () {
                $this->schedule->job(new ParseShipMatrixDownload());
            }
        );
    }
}
