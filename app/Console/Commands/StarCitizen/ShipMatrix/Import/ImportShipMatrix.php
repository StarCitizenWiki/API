<?php

declare(strict_types=1);

namespace App\Console\Commands\StarCitizen\ShipMatrix\Import;

use App\Jobs\StarCitizen\Vehicle\Import\ImportShipMatrix as ImportShipMatrixJob;
use Illuminate\Console\Command;

/**
 * Import newest downloaded ship matrix file into the database
 */
class ImportShipMatrix extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ship-matrix:import {--file=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import the newest downloaded ship matrix file into the database';


    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $this->info('Dispatching Ship Matrix Parsing Job');

        $file = $this->option('file');
        if ($file !== null) {
            $file = explode('vehicles', $this->option('file'))[1] ?? null;
        }

        ImportShipMatrixJob::dispatch($file);

        return 0;
    }
}
