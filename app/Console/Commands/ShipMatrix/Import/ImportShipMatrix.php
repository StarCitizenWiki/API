<?php

declare(strict_types=1);

namespace App\Console\Commands\ShipMatrix\Import;

use App\Jobs\Api\StarCitizen\Vehicle\Import\ImportShipMatrix as ImportShipMatrixJob;
use Illuminate\Bus\Dispatcher;
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
    protected $signature = 'ship-matrix:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import the newest downloaded ship matrix file into the database';

    /**
     * @var Dispatcher
     */
    private Dispatcher $dispatcher;

    /**
     * Create a new command instance.
     *
     * @param Dispatcher $dispatcher
     */
    public function __construct(Dispatcher $dispatcher)
    {
        parent::__construct();

        $this->dispatcher = $dispatcher;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $this->info('Dispatching Ship Matrix Parsing Job');
        $this->dispatcher->dispatch(new ImportShipMatrixJob());

        return 0;
    }
}
