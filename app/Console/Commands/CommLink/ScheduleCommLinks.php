<?php

declare(strict_types=1);

namespace App\Console\Commands\CommLink;

use App\Jobs\Rsi\CommLink\Download\DownloadMissingCommLinks;
use App\Jobs\Rsi\CommLink\Import\ImportCommLinks;
use Illuminate\Console\Command;

class ScheduleCommLinks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'comm-link:schedule';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download missing Comm-Links and queue imports.';

    public function handle(): int
    {
        DownloadMissingCommLinks::withChain([
            new ImportCommLinks(30),
        ])->dispatch();

        return self::SUCCESS;
    }
}
