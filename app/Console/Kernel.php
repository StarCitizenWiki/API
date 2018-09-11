<?php declare(strict_types = 1);

namespace App\Console;

use App\Events\Rsi\CommLink\NewCommLinksDownloaded;
use App\Jobs\Api\StarCitizen\Stat\DownloadStats;
use App\Jobs\Api\StarCitizen\Vehicle\DownloadShipMatrix;
use App\Jobs\Api\StarCitizen\Vehicle\Parser\ParseShipMatrixDownload;
use App\Jobs\Rsi\CommLink\DownloadMissingCommLinks;
use App\Jobs\Rsi\CommLink\GenerateNewCommLinksMail;
use App\Jobs\Rsi\CommLink\Parser\ParseCommLinkDownload;
use App\Jobs\Rsi\CommLink\ReDownloadDbCommLinks;
use App\Models\Rsi\CommLink\CommLink;
use Goutte\Client;
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
        \App\Console\Commands\DownloadShipMatrix::class,
        \App\Console\Commands\DownloadStats::class,
        \App\Console\Commands\ImportShipMatrix::class,
        \App\Console\Commands\ImportCommLinks::class,
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

        $schedule->job(new DownloadStats())->dailyAt('20:00');
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
     * Comm Link related Jobs
     */
    private function scheduleCommLinkJobs()
    {
        /** Check for new Comm Links */
        $missingOffset = optional(CommLink::orderByDesc('cig_id')->first())->cig_id ?? 0;
        $this->schedule->job(new DownloadMissingCommLinks(new Client()))->hourly()->withoutOverlapping()->then(
            function () use ($missingOffset) {
                $this->schedule->job(new ParseCommLinkDownload($missingOffset));
            }
        )->then(
            function () use ($missingOffset) {
                $newCommLinks = CommLink::where('cig_id', '>', $missingOffset)->get();

                $this->events->dispatch(new NewCommLinksDownloaded($newCommLinks));
            }
        );

        /** Re-Download all Comm Links monthly */
        $this->schedule->job(new ReDownloadDbCommLinks())->monthly()->then(
            function () {
                $this->schedule->job(new ParseCommLinkDownload());
            }
        );
    }

    /**
     * Ship Matrix related Jobs
     */
    private function scheduleShipMatrixJobs()
    {
        $this->schedule->job(new DownloadShipMatrix())->weekly()->then(
            function () {
                $this->schedule->job(new ParseShipMatrixDownload());
            }
        );

        // TODO Stündlicher Check auf neue Schiffe
    }
}
