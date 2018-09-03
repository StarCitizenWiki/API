<?php declare(strict_types = 1);

namespace App\Console;

use App\Jobs\Api\StarCitizen\Stat\DownloadStats;
use App\Jobs\Api\StarCitizen\Vehicle\DownloadShipMatrix;
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
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     *
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->job(new DownloadStats())->dailyAt('20:00');

        $schedule->job(new DownloadShipMatrix())->weekly()->then(
            function () {
                $this->call('import:shipmatrix');
            }
        );
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
}
