<?php

declare(strict_types=1);

namespace App\Jobs\Rsi\CommLink\Import;

use App\Jobs\Rsi\CommLink\Image\CreateImageMetadata;
use App\Jobs\Rsi\CommLink\Image\DispatchImageHashes;
use App\Jobs\Rsi\CommLink\Translate\TranslateCommLinks;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImportCommLinks implements ShouldQueue
{
    use Queueable;

    public int $timeout = 120;

    public function __construct(public readonly int $modifiedFolderTime = 5) {}

    public function handle(): void
    {
        $directories = $this->filterDirectories('comm_links', $this->modifiedFolderTime);

        if ($directories->isEmpty()) {
            return;
        }

        $jobs = [];
        $commLinkIds = [];

        foreach ($directories as $directory) {
            $file = $this->latestFile($directory);

            if ($file === null) {
                continue;
            }

            $commLinkId = (int) $directory;
            $jobs[] = new ImportCommLink($commLinkId, $file);
            $commLinkIds[] = $commLinkId;
        }

        if ($jobs === []) {
            return;
        }

        Bus::batch($jobs)
            ->name('Comm-Link import')
            ->allowFailures()
            ->then(function () use ($commLinkIds): void {
                if ($commLinkIds === []) {
                    return;
                }

                CreateImageMetadata::dispatch($commLinkIds);
                DispatchImageHashes::dispatch($commLinkIds);

                if (
                    (bool) config('services.comm_links.auto_translate_after_import', false)
                    && filled(config('services.deepl.auth_key'))
                ) {
                    TranslateCommLinks::dispatch($commLinkIds);
                }
            })
            ->dispatch();
    }

    private function latestFile(string $directory): ?string
    {
        $files = Storage::disk('comm_links')->files($directory);

        if ($files === []) {
            return null;
        }

        sort($files);
        $file = end($files);

        if ($file === false) {
            return null;
        }

        return Str::afterLast($file, '/');
    }

    /**
     * Filter folders that where created in the last X minutes on a given disk
     *
     * @param  string  $disk  The disk name to filter
     * @param  int  $findTimeMinutes  Include directories created in the last X minutes or all if -1
     */
    private function filterDirectories(string $disk, int $findTimeMinutes): Collection
    {
        $now = Carbon::now()->subMinutes($findTimeMinutes);

        return collect(Storage::disk($disk)->directories())
            ->filter(
                function (string $dir) use ($disk, $now, $findTimeMinutes) {
                    $mTime = Carbon::createFromTimestamp(File::lastModified(Storage::disk($disk)->path($dir)));

                    if ($findTimeMinutes === -1) {
                        return true;
                    }

                    return $mTime->greaterThanOrEqualTo($now);
                }
            );
    }
}
