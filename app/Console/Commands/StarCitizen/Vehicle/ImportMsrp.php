<?php

namespace App\Console\Commands\StarCitizen\Vehicle;

use App\Jobs\StarCitizen\Vehicle\ImportMsrp as ImportMsrpJob;
use Illuminate\Console\Command;

class ImportMsrp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vehicles:import-msrp';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import all MSRPs by requesting the pledge-store upgrade api endpoint';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Requesting MSRPs');

        ImportMsrpJob::dispatch();

        return 0;
    }
}
