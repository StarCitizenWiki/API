<?php

declare(strict_types=1);

namespace App\Traits\Jobs;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

trait GetFoldersTrait
{
    private function filterDirectories(string $disk, int $findTimeMinutes): Collection
    {
        $now = Carbon::now();

        return collect(Storage::disk($disk)->directories())
            ->filter(
                function (string $dir) use ($disk, $now, $findTimeMinutes) {
                    $mTime = Carbon::createFromTimestamp(File::lastModified(Storage::disk($disk)->path($dir)));

                    if ($findTimeMinutes === -1) {
                        return true;
                    }

                    return $now->subMinutes(5)->lessThanOrEqualTo($mTime);
                }
            );
    }
}
