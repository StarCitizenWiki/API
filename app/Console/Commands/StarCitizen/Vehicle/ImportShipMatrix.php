<?php

declare(strict_types=1);

namespace App\Console\Commands\StarCitizen\Vehicle;

use App\Jobs\StarCitizen\Vehicle\ImportShipMatrix as ImportShipMatrixJob;
use App\Support\Filters\FilterCache;
use Illuminate\Console\Command;

class ImportShipMatrix extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vehicles:import-ship-matrix';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download the latest ship matrix and import it into the database';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Dispatching Ship Matrix Download and Import Job');

        ImportShipMatrixJob::dispatch();
        FilterCache::bust(FilterCache::NAMESPACE_SHIPMATRIX);

        return 0;
    }
}
