<?php

declare(strict_types=1);

namespace App\Console\Commands\Rsi\CommLink\Download\Image;

use App\Jobs\Rsi\CommLink\Download\Image\DownloadCommLinkImage;
use App\Jobs\Rsi\CommLink\Download\Image\DownloadCommLinkImages as DownloadCommLinkImagesJob;
use Illuminate\Console\Command;

/**
 * Command Wrapper for Download Comm-Link Job
 * @see DownloadCommLinkImagesJob
 * @see DownloadCommLinkImage
 */
class DownloadCommLinkImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'comm-links:download-images';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download all non-local Comm-Link Images. Jobs are dispatched on the \'comm_link_images\' queue';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        dispatch(new DownloadCommLinkImagesJob());

        return 0;
    }
}
