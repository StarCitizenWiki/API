<?php
/**
 * User: Keonie
 * Date: 16.08.2018 19:43
 */

namespace App\Jobs\Api\StarCitizen\Starmap;

use App\Jobs\AbstractBaseDownloadData;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

/**
 * Class DownloadStarmap
 * @package App\Jobs\Api\StarCitizen\Starmap
 */
class DownloadStarmap extends AbstractBaseDownloadData implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    const STARSYSTEM_ENDPOINT = '/starsystem/index';
    private const STARSYSTEM_DISK = 'starsystem';

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        app('Log')::info('Starting Starmap Download');

    }
}