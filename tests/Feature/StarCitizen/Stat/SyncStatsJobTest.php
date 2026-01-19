<?php

declare(strict_types=1);

use App\Jobs\StarCitizen\Stat\DownloadStats;
use App\Jobs\StarCitizen\Stat\ImportStat;
use App\Jobs\StarCitizen\Stat\SyncStats;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Bus;

it('dispatches download with a chained import using a shared filename', function (): void {
    Bus::fake();

    Carbon::setTestNow(Carbon::parse('2025-01-02 03:04:05'));

    $job = new SyncStats;
    $job->handle();

    $fileName = 'stats_2025-01-02.json';
    $year = 2025;

    Bus::assertChained([
        new DownloadStats($fileName, $year),
        new ImportStat($fileName, $year),
    ]);

    Carbon::setTestNow();
});
