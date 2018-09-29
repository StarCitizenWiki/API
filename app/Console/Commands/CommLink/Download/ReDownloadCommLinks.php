<?php declare(strict_types = 1);

namespace App\Console\Commands\CommLink\Download;

use App\Jobs\Rsi\CommLink\Parser\ParseCommLinkDownload;
use App\Jobs\Rsi\CommLink\ReDownloadDbCommLinks;
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
    protected $signature = 'download:new-comm-link-versions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Re-Downloads all Comm Links and parses them';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        ReDownloadDbCommLinks::withChain(
            [
                new ParseCommLinkDownload(),
            ]
        )->dispatch();
    }
}
