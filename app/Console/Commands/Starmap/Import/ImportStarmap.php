<?php

namespace App\Console\Commands\Starmap\Import;

use App\Jobs\Api\StarCitizen\Starmap\Import\ImportStarmap;
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

        ImportStarmap::dispatch();

        return 0;
    }
}
