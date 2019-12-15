<?php

declare(strict_types=1);

namespace App\Jobs\Transcript\Translate;

use App\Models\Transcript\Transcript;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Translate new Comm-Links.
 */
class TranslateTranscripts implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private $limit;

    /**
     * TranslateTranscripts constructor.
     *
     * @param int $limit Max translation jobs to run
     */
    public function __construct(int $limit = 0)
    {
        $this->limit = $limit;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        app('Log')::info('Starting Transcript Translations');

        $jobLimit = 0 === $this->limit ? PHP_INT_MAX : $this->limit;
        $count = 0;

        Transcript::query()->chunk(
            100,
            static function (Collection $transcripts) use ($jobLimit, &$count) {
                $transcripts->each(
                    static function (Transcript $transcript) use ($jobLimit, &$count) {
                        if ($count < $jobLimit && null === optional($transcript->german())->translation) {
                            ++$count;
                            dispatch(new TranslateTranscript($transcript));
                        }
                    }
                );
            }
        );
    }
}
