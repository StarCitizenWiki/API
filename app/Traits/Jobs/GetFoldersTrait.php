<?php

declare(strict_types=1);

namespace App\Traits\Jobs;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

trait GetFoldersTrait
{
    /**
     * Filter folders that where created in the last X minutes on a given disk
     *
     * @param string $disk The disk name to filter
     * @param int $findTimeMinutes Include directories created in the last X minutes or all if -1
     *
     * @return Collection
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
