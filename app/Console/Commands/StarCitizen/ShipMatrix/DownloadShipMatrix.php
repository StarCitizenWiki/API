<?php

declare(strict_types=1);

namespace App\Console\Commands\StarCitizen\ShipMatrix;

use App\Jobs\StarCitizen\Vehicle\CheckShipMatrixStructure;
use App\Jobs\StarCitizen\Vehicle\DownloadShipMatrix as DownloadShipMatrixJob;
use App\Jobs\StarCitizen\Vehicle\Import\ImportShipMatrix;
use Illuminate\Bus\Dispatcher;
use Illuminate\Console\Command;

/**
 * Download the Ship Matrix and optionally import it
 */
class DownloadShipMatrix extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ship-matrix:download {--i|import : Import the ship-matrix after the download}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download the ship matrix and optionally import it';

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
        if ($this->option('import') === true) {
            $this->info('Downloading Ship Matrix and starting import');
            DownloadShipMatrixJob::withChain(
                [
                    new CheckShipMatrixStructure(),
                    new ImportShipMatrix(),
                ]
            )->dispatch();
        } else {
            $this->info('Dispatching Ship Matrix Download Job');
            $this->dispatcher->dispatch(new DownloadShipMatrixJob());
        }

        return 0;
    }
}
