<?php

namespace App\Jobs\Rsi\CommLink;

use App\Models\Rsi\CommLink\CommLink;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class SyncImageIds implements ShouldQueue
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
     */
    public function handle()
    {
        $commLinks = CommLink::query()->where('cig_id', '>=', $this->offset)->get();

        $commLinks->each(function ($commLink) {
            if (!Storage::disk('comm_links')->exists((string) $commLink->cig_id)) {
                return;
            }

            dispatch(new SyncImageId($commLink));
        });
    }
}
