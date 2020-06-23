<?php declare(strict_types = 1);

namespace App\Console\Commands\CommLink\Download;

use App\Jobs\Rsi\CommLink\Download\DownloadMissingCommLinks;
use Goutte\Client;
use Illuminate\Console\Command;

class DownloadCommLinks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'download:comm-links';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download all available Comm-Links';

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
