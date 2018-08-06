<?php declare(strict_types = 1);

use App\Jobs\Api\StarCitizen\Vehicle\DownloadShipMatrix;
use App\Jobs\Api\StarCitizen\Stat\DownloadStats;

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
        Bus::dispatchNow(new DownloadShipMatrix());
    }
)->describe('Starts the Ship Matrix Download Job');

Artisan::command(
    'download:stats',
    function () {
        $this->info("Dispatching Stats Download");
        Bus::dispatchNow(new DownloadStats());
    }
)->describe('Starts the Stats Download Job');
