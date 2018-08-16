<?php declare(strict_types = 1);

use App\Jobs\Api\StarCitizen\Stat\DownloadStats;
use App\Jobs\Api\StarCitizen\Vehicle\DownloadShipMatrix;
use App\Jobs\Api\StarCitizen\Vehicle\Parser\ParseShipMatrixDownload;
use Symfony\Component\Console\Input\InputArgument;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/
Artisan::command(
    'download:shipmatrix',
    function () {
        $this->info("Dispatching Ship Matrix Download");

        $force = false;
        if (null !== $this->argument('force')) {
            $force = true;
        }

        Bus::dispatchNow(new DownloadShipMatrix($force));

        if (null !== $this->argument('import')) {
            Bus::dispatch(new ParseShipMatrixDownload());
        }
    }
)
    ->addArgument('force', InputArgument::OPTIONAL, 'Force Download, Overwrite File if exist')
    ->addArgument('import', InputArgument::OPTIONAL, 'Import Ships after Download')
    ->describe('Starts the Ship Matrix Download Job');

Artisan::command(
    'import:shipmatrix',
    function () {
        $this->info("Dispatching Ship Matrix Parsing Job");
        if (null !== $this->argument('file')) {
            $this->info("Using File {$this->argument('file')}");

            Bus::dispatch(new ParseShipMatrixDownload($this->argument('file')));
        } else {
            Bus::dispatchNow(new ParseShipMatrixDownload());
        }
    }
)
    ->addArgument('file', InputArgument::OPTIONAL, 'File on Vehicle Disk to import')
    ->describe('Import Shipmatrix into DB');

Artisan::command(
    'import:stats',
    function () {
        $this->info("Dispatching Stats Download");
        Bus::dispatchNow(new DownloadStats());
    }
)->describe('Starts the Stats Download Job');
