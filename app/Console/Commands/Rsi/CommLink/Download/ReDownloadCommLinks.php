<?php

declare(strict_types=1);

namespace App\Console\Commands\Rsi\CommLink\Download;

use App\Jobs\Rsi\CommLink\Download\ReDownloadDbCommLinks;
use App\Jobs\Rsi\CommLink\Import\ImportCommLinks;
use Illuminate\Console\Command;

/**
 * Class ReDownloadCommLinks
 */
class ReDownloadCommLinks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'comm-links:download-new-versions {--skip=true}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Re-Download all Database Comm-Links and parse them.' .
                            'Pass "--skip=false" to not skip already downloaded Comm-Links.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $skip = $this->option('skip');
        if ($skip === true || $skip === 'true' || $skip === '1') {
            $skip = true;
        } else {
            $skip = false;
        }

        ReDownloadDbCommLinks::withChain(
            [
                new ImportCommLinks(-1),
            ]
        )->dispatch($skip);

        return 0;
    }
}
