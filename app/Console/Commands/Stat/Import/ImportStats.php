<?php declare(strict_types = 1);

namespace App\Console\Commands\Stat\Import;

use App\Jobs\Api\StarCitizen\Stat\DownloadStats;
use App\Jobs\Api\StarCitizen\Stat\Parser\ParseStat;
use Illuminate\Bus\Dispatcher;
use Illuminate\Console\Command;

/**
 * Class DownloadStats
 */
class ImportStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:stats {--d|download : Download Stats before importing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports Stats';

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
     * @return int
     */
    public function handle(): int
    {
        if ($this->option('download')) {
            $this->info('Downloading Stats and starting import');
            DownloadStats::withChain(
                [
                    new ParseStat(),
                ]
            )->dispatch();
        } else {
            $this->info('Starting Stat Import');
            $this->dispatcher->dispatchNow(new ParseStat());
        }

        return 0;
    }
}
