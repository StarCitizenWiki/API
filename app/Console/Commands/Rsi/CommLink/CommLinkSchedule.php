<?php

declare(strict_types=1);

namespace App\Console\Commands\Rsi\CommLink;

use App\Jobs\Rsi\CommLink\Download\DownloadMissingCommLinks;
use App\Jobs\Rsi\CommLink\Import\ImportCommLinks;
use Illuminate\Console\Command;

class CommLinkSchedule extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'comm-links:schedule';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download missing Comm-Links. Parse the download, create metadata and hashes. ' .
    'If a DeepL API key is set, Comm-Links will be translated. ' .
    'If a MediaWiki Account is configured, Wiki Comm-Link pages will be created';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        DownloadMissingCommLinks::dispatch()->chain(
            [
                new ImportCommLinks(30),
            ]
        );

        return 0;
    }
}
