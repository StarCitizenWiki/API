<?php

declare(strict_types=1);

namespace App\Jobs\Rsi\CommLink\Parser;

use App\Jobs\CommLink\SyncImageIds;
use App\Models\Rsi\CommLink\CommLink;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

/**
 * Dispatches a ParseCommLink Job for the newest file in every Comm-Link Folder.
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
    private $offset;

    /**
     * @var string Job mode parse or syncimageids
     */
    private $mode;

    /**
     * Create a new job instance.
     *
     * @param int    $offset Directory Offset
     * @param string $mode
     */
    public function __construct(int $offset = 0, string $mode = 'parse')
    {
        $this->offset = $offset;
        $this->mode = $mode;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $commLinks = CommLink::query()->get();
        $commLinks = $commLinks->keyBy('cig_id');

        collect(Storage::disk('comm_links')->directories())->each(
            function ($commLinkDir) use ($commLinks) {
                if ((int) $commLinkDir >= $this->offset) {
                    $file = Arr::last(Storage::disk('comm_links')->files($commLinkDir));

                    if (null !== $file) {
                        $file = preg_split('/\/|\\\/', $file);
                        $commLink = $commLinks->get((int) $commLinkDir, null);

                        if ('parse' === $this->mode) {
                            dispatch(new ParseCommLink((int) $commLinkDir, Arr::last($file), $commLink));
                        } elseif (null !== $commLink) {
                            dispatch(new SyncImageIds((int) $commLinkDir, $commLink->file, $commLink));
                        }
                    }
                }
            }
        );
    }
}
