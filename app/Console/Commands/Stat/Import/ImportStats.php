<?php

declare(strict_types=1);

namespace App\Console\Commands\Stat\Import;

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
    protected $signature = 'stats:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import the newest downloaded funding statistics file into the database. WARNING: Creates a new database record based on the latest downloaded file, can create DUPLICATE records';

    /**
     * @var Dispatcher
     */
    private Dispatcher $dispatcher;

    /**
     * Create a new command instance.
     *
     * @param Dispatcher $dispatcher
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
        $this->info('Starting funding statistics import');
        $this->dispatcher->dispatchNow(new ParseStat());

        return 0;
    }
}
