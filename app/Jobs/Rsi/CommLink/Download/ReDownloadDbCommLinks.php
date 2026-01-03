<?php

declare(strict_types=1);

namespace App\Jobs\Rsi\CommLink\Download;

use App\Models\Rsi\CommLink\CommLink;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class ReDownloadDbCommLinks implements ShouldQueue
{
    use Queueable;

    public const FIRST_COMM_LINK_ID = 12663;

    public int $timeout = 120;

    public function __construct(public readonly bool $skipExisting = true) {}

    public function handle(): void
    {
        $latestDbId = CommLink::query()->max('cig_id');

        if ($latestDbId === null) {
            $this->fail(new RuntimeException('No Comm-Links found in database.'));

            return;
        }

        Log::info('Re-downloading Comm-Links from database.', ['max_id' => $latestDbId]);

        for ($id = self::FIRST_COMM_LINK_ID; $id <= $latestDbId; $id++) {
            dispatch(new DownloadCommLink($id, $this->skipExisting))
                ->delay(($id - self::FIRST_COMM_LINK_ID) * 30);
        }
    }
}
