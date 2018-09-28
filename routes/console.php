<?php declare(strict_types = 1);

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
    'download:missing-comm-links',
    function () {
        dispatch(app(\App\Jobs\Rsi\CommLink\DownloadMissingCommLinks::class));
    }
);
