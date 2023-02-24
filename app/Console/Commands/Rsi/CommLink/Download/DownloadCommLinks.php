<?php

declare(strict_types=1);

namespace App\Console\Commands\Rsi\CommLink\Download;

use App\Jobs\Rsi\CommLink\Download\DownloadMissingCommLinks;
use Illuminate\Console\Command;

class DownloadCommLinks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'comm-links:download-all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download all Comm-Links starting at ID 12663';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $this->info('Downloading all Comm-Links');
        dispatch(new DownloadMissingCommLinks());

        return 0;
    }
}
