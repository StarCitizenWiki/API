<?php declare(strict_types = 1);

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
    protected $signature = 'download:new-comm-link-versions {--s|skip : Skip existing Comm-Links}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Re-Downloads all Comm-Links and parses them';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        ReDownloadDbCommLinks::withChain(
            [
                new ParseCommLinkDownload(),
            ]
        )->dispatch(true);

        return 0;
    }
}
