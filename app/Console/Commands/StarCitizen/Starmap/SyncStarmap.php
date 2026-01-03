<?php

declare(strict_types=1);

namespace App\Console\Commands\StarCitizen\Starmap;

use App\Jobs\StarCitizen\Starmap\Sync\SyncStarmap as SyncStarmapJob;
use Illuminate\Console\Command;

class SyncStarmap extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'starmap:sync
                            {--f|force : Force Download, Overwrite File if exist}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Downloads and imports the latest starmap';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Dispatching Starmap Sync');

        SyncStarmapJob::dispatch($this->option('force') === true);

        return 0;
    }
}
