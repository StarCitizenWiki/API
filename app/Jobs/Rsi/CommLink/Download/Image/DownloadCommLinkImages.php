<?php declare(strict_types = 1);

namespace App\Jobs\Rsi\CommLink\Download\Image;

use App\Models\Rsi\CommLink\Image\Image;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Dispatches DownloadCommLinkImage jobs for all non-local images.
 * @see DownloadCommLinkImage
 */
class DownloadCommLinkImages implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        app('Log')::info('Starting Comm-Link Image download');

        Image::query()->where('local', false)->chunk(
            100,
            function (Collection $images) {
                $images->each(
                    function (Image $image) {
                        dispatch(new DownloadCommLinkImage($image))->onQueue('comm_link_images');
                    }
                );
            }
        );
    }
}
