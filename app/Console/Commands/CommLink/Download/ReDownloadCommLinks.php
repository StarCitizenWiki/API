<?php

declare(strict_types=1);

namespace App\Console\Commands\CommLink\Download;

use App\Jobs\Rsi\CommLink\Download\ReDownloadDbCommLinks;
use App\Jobs\Rsi\CommLink\Parser\ParseCommLinkDownload;
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
    protected $signature = 'comm-links:download-new-versions {--s|skip : Skip existing Comm-Links}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Re-Download all Database Comm-Links and parse them';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        if (!$this->hasOption('skip')) {
            $skip = true;
        } else {
            $skip = $this->hasOption('skip');
        }

        ReDownloadDbCommLinks::withChain(
            [
                new ParseCommLinkDownload(),
            ]
        )->dispatch($skip);

        return 0;
    }
}
