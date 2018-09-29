<?php declare(strict_types = 1);

namespace App\Console\Commands\ShipMatrix\Import;

use App\Jobs\Api\StarCitizen\Vehicle\DownloadShipMatrix;
use App\Jobs\Api\StarCitizen\Vehicle\Parser\ParseShipMatrixDownload;
use Illuminate\Bus\Dispatcher;
use Illuminate\Console\Command;

/**
 * Import given or latest shipmatrix file into db
 */
class ImportShipMatrix extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:shipmatrix {--d|download : Download Shipmatrix}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import Shipmatrix File into the Database';

    /**
     * @var \Illuminate\Bus\Dispatcher
     */
    private $dispatcher;

    /**
     * Create a new command instance.
     *
     * @param \Illuminate\Bus\Dispatcher $dispatcher
     */
    public function __construct(Dispatcher $dispatcher)
    {
        parent::__construct();

        $this->dispatcher = $dispatcher;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->option('download')) {
            $this->info('Downloading Ship Matrix and starting import');
            DownloadShipMatrix::withChain(
                [
                    new ParseShipMatrixDownload(),
                ]
            )->dispatch();
        } else {
            $this->info('Dispatching Ship Matrix Parsing Job');
            $this->dispatcher->dispatchNow(new ParseShipMatrixDownload());
        }
    }
}
