<?php declare(strict_types = 1);

namespace App\Console\Commands\Stat;

use App\Jobs\Api\StarCitizen\Stat\DownloadStats as DownloadStatsJob;
use App\Jobs\Api\StarCitizen\Stat\Parser\ParseStat;
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
    protected $signature = 'download:stats
                            {--f|force : Force Download, Overwrite File if exist} 
                            {--i|import : Import Stats after Download}';

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
     * @return void
     */
    public function handle()
    {
        $this->info("Dispatching Stats Download");

        if ($this->option('force')) {
            $this->info('Forcing Download');
        }

        $this->dispatcher->dispatchNow(new DownloadStatsJob($this->option('force')));

        if ($this->option('import')) {
            $this->info('Starting Import');
            $this->dispatcher->dispatchNow(new ParseStat());
        }
    }
}
