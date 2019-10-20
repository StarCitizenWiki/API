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

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        app('Log')::info('Starting Transcript Translations');

        Transcript::query()->chunk(
            100,
            static function (Collection $transcripts) {
                $transcripts->each(
                    static function (Transcript $transcript) {
                        if (null === optional($transcript->german())->translation) {
                            dispatch(new TranslateTranscript($transcript));
                        }
                    }
                );
            }
        );
    }
}
