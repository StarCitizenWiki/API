<?php

namespace App\Console\Commands\StarCitizen\Vehicle;

use Illuminate\Console\Command;

class ImportLoaner extends Command
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
     */
    public function handle(): int
    {
        $this->info('Importing Loaners');

        \App\Jobs\StarCitizen\Vehicle\ImportLoaner::dispatch();

        return 0;
    }
}
