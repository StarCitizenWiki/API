<?php

namespace App\Console\Commands\StarCitizen\Vehicle;

use App\Console\Commands\AbstractQueueCommand;

class ImportLoaner extends AbstractQueueCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vehicles:import-loaner';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import all Loaners';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $this->info('Importing Loaners');

        \App\Jobs\StarCitizen\Vehicle\Import\ImportLoaner::dispatch();

        return 0;
    }
}
