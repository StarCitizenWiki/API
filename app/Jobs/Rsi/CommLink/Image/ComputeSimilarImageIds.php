<?php

declare(strict_types=1);

namespace App\Jobs\Rsi\CommLink\Image;

use App\Models\Rsi\CommLink\Image\Image;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class ComputeSimilarImageIds implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private Image $image;

    public function __construct(Image $image)
    {
        $this->image = $image;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->image->refresh();
        if ($this->image->base_image_id !== null) {
            return;
        }

        $this->image->similarImages(95, 250)->each(function (Image $duplicate) {
            unset($duplicate->similarity, $duplicate->similarity_method, $duplicate->pdq_distance);

            if ($duplicate->base_image_id === $this->image->id) {
                return;
            }

            $duplicate->update([
                'base_image_id' => $this->image->id,
            ]);
        });
    }
}
