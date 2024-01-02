<?php

namespace App\Console\Commands\StarCitizen\Starmap;

use App\Jobs\StarCitizen\Starmap\Import\ImportStarmap as ImportStarmapJob;
use Illuminate\Console\Command;

class ImportStarmap extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'starmap:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports the newest downloaded starmap';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $this->info('Importing starmap');

        ImportStarmapJob::dispatch();

        return 0;
    }
}
