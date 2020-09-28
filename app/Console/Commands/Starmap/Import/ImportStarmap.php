<?php

namespace App\Console\Commands\Starmap\Import;

use App\Jobs\Api\StarCitizen\Starmap\Parser\ParseStarmap;
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
    protected $description = 'Command description';


    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        ParseStarmap::dispatch();

        return 0;
    }
}
