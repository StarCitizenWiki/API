<?php declare(strict_types = 1);

namespace App\Console\Commands;

use App\Jobs\Api\StarCitizen\Stat\DownloadStats as DownloadStatsJob;
use Illuminate\Bus\Dispatcher;
use Illuminate\Console\Command;

/**
 * Class DownloadStats
 */
class DownloadStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'download:stats';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Starts the Stats Download Job';

    /**
     * @var \Illuminate\Bus\Dispatcher
     */
    private $dispatcher;

    /**
     * Create a new command instance.
     *
     * @param \Illuminate\Bus\Dispatcher $dispatcher
     */
    public function __construct(Dispatcher $dispatcher)
    {
        parent::__construct();

        $this->dispatcher = $dispatcher;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info("Dispatching Stats Download");
        $this->dispatcher->dispatchNow(new DownloadStatsJob());
    }
}
