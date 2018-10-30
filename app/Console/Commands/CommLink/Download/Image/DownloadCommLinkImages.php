<?php declare(strict_types = 1);

namespace App\Console\Commands\CommLink\Download\Image;

use App\Jobs\Rsi\CommLink\Download\Image\DownloadCommLinkImages as DownloadCommLinkImagesJob;
use Illuminate\Console\Command;

/**
 * Command Wrapper for Download Comm-Link Job
 */
class DownloadCommLinkImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'download:comm-link-images';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatch Download of Comm-Link Images';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        dispatch(new DownloadCommLinkImagesJob());
    }
}
