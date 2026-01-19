<?php

declare(strict_types=1);

namespace App\Jobs\StarCitizen\Stat;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SyncStats implements ShouldQueue
{
    use Queueable;

    public int $timeout = 120;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $date = now();
        $statFileName = sprintf('stats_%s.json', $date->format('Y-m-d'));
        $year = $date->year;

        DownloadStats::withChain([
            new ImportStat($statFileName, $year),
        ])->dispatch($statFileName, $year);
    }
}
