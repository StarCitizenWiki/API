<?php

declare(strict_types=1);

namespace App\Jobs\Rsi\CommLink\Image;

use App\Models\Rsi\CommLink\Image\Image;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ComputeSimilarImageIds implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $timeout = 300;

    public function __construct(public readonly int $imageId)
    {
        //
    }

    public function handle(): void
    {
        $image = Image::query()
            ->with(['hash'])
            ->find($this->imageId);

        if ($image === null) {
            return;
        }

        if ($image->base_image_id !== null) {
            return;
        }

        $similarImages = $image->similarImages(95, 50);

        foreach ($similarImages as $duplicate) {
            // Skip if duplicate already points to this image
            if ($duplicate->base_image_id === $image->id) {
                continue;
            }

            unset(
                $duplicate->similarity,
                $duplicate->similarity_method,
                $duplicate->pdq_hash,
                $duplicate->pdq_quality,
                $duplicate->distance
            );

            $duplicate->update(['base_image_id' => $image->id]);
        }
    }
}
