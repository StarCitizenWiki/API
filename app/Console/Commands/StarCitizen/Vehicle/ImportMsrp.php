<?php

namespace App\Console\Commands\StarCitizen\Vehicle;

use App\Console\Commands\AbstractQueueCommand;
use App\Jobs\StarCitizen\Vehicle\Import\ImportMsrp as ImportMsrpJob;

class ImportMsrp extends AbstractQueueCommand
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
     *
     * @return int
     */
    public function handle(): int
    {
        $this->info('Requesting MSRPs');

        ImportMsrpJob::dispatch();

        return 0;
    }
}
