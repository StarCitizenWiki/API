<?php declare(strict_types = 1);

namespace App\Jobs\Rsi\CommLink\Parser;

use App\Models\Rsi\CommLink\CommLink;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

/**
 * Dispatches a ParseCommLink Job for the newest file in every Comm Link Folder
 */
class ParseCommLinkDownload implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @var int Offset to start parsing from
     */
    private $offset = 0;

    /**
     * Create a new job instance.
     *
     * @param int $offset Directory Offset
     */
    public function __construct(int $offset = 0)
    {
        $this->offset = $offset;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $commLinks = CommLink::query()->get();
        $commLinks = $commLinks->keyBy('cig_id');

        collect(Storage::disk('comm_links')->directories())->each(
            function ($commLinkDir) use ($commLinks) {
                if (intval($commLinkDir) >= $this->offset) {
                    $file = array_last(Storage::disk('comm_links')->files($commLinkDir));

                    if (null !== $file) {
                        dispatch(new ParseCommLink(intval($commLinkDir), $file, $commLinks->get($commLinkDir, null)));
                    }
                }
            }
        );
    }
}
