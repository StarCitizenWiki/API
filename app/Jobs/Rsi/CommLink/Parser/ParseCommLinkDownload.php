<?php declare(strict_types = 1);

namespace App\Jobs\Rsi\CommLink\Parser;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ParseCommLinkDownload implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

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
        foreach (Storage::disk('comm_links')->directories() as $commLinkDir) {
            $file = scandir(Storage::disk('comm_links')->path($commLinkDir), SCANDIR_SORT_DESCENDING)[0];

            dispatch(new ParseCommLink(intval($commLinkDir), $file));
        }
    }
}
