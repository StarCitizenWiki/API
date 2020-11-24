<?php

declare(strict_types=1);

namespace App\Jobs\Rsi\CommLink\Download;

use App\Jobs\AbstractBaseDownloadData as BaseDownloadData;
use App\Models\Rsi\CommLink\CommLink;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use InvalidArgumentException;

/**
 * Re-Downloads a new Version of all existing Database Comm-Links
 */
class ReDownloadDbCommLinks extends BaseDownloadData implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public const FIRST_COMM_LINK_ID = 12663;

    private bool $skipExisting;

    /**
     * ReDownloadDbCommLinks constructor.
     *
     * @param bool $skipExisting Don't download existing Comm-Links
     */
    public function __construct(bool $skipExisting = false)
    {
        $this->skipExisting = $skipExisting;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        app('Log')::info('Re-Downloading all DB Comm-Links');

        $latestDbPost = CommLink::query()->orderByDesc('cig_id')->first();
        if (null === $latestDbPost) {
            $this->fail(new InvalidArgumentException('No Comm-Links in DB Found'));

            return;
        }

        app('Log')::info(
            "Latest DB Comm-Link CIG ID: {$latestDbPost->cig_id}",
            [
                'cig_id' => $latestDbPost->cig_id,
            ]
        );

        $this->makeClient(false);

        for ($id = self::FIRST_COMM_LINK_ID; $id <= $latestDbPost->cig_id; $id++) {
            dispatch(new DownloadCommLink($id, $this->skipExisting));
        }
    }
}
