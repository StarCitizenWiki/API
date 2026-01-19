<?php

declare(strict_types=1);

namespace App\Console\Commands\StarCitizen;

use App\Jobs\StarCitizen\Stat\SyncStats as SyncStatsJob;
use Illuminate\Console\Command;

class SyncStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stats:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download and import funding statistics.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Dispatching Stats Sync');

        SyncStatsJob::dispatch();

        return Command::SUCCESS;
    }
}
