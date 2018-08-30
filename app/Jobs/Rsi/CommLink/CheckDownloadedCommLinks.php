<?php declare(strict_types = 1);

namespace App\Jobs\Rsi\CommLink;

use App\Models\Rsi\CommLink\CommLink;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

/**
 * Checks downloaded Comm Links against the live version
 */
class CheckDownloadedCommLinks implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    const FIRST_COMM_LINK_ID = 12663;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $latestDbPost = CommLink::orderBy('cig_id')->first();
        if (null === $latestDbPost) {
            $this->fail(new \InvalidArgumentException('No Comm Links in DB Found'));

            return;
        }

        for ($id = self::FIRST_COMM_LINK_ID; $id <= $latestDbPost->cig_id; $id++) {
            dispatch(new DownloadCommLink(($id)))->onQueue('comm_links');
        }
    }
}
