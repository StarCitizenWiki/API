<?php

declare(strict_types=1);

namespace App\Jobs\Rsi\CommLink\Image;

use App\Models\Rsi\CommLink\Image\Image;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Queue\Queueable;

class DispatchImageHashes implements ShouldQueue
{
    use Queueable;

    /**
     * @param  array<int, int>  $commLinkIds
     */
    public function __construct(public readonly array $commLinkIds = []) {}

    public function handle(): void
    {
        $query = Image::query()
            ->whereHas('commLinks')
            ->where(function (Builder $query) {
                $query->whereRelation('metadata', 'mime', 'LIKE', 'video%')
                    ->orWhereRelation('metadata', 'mime', 'LIKE', 'image%');
            })
            ->where('src', 'NOT LIKE', '%.svg')
            ->where('src', 'NOT LIKE', '%.tiff')
            ->whereDoesntHave('hash');

        if ($this->commLinkIds !== []) {
            $query->whereHas('commLinks', function (Builder $builder): void {
                $builder->whereIn('cig_id', $this->commLinkIds);
            });
        }

        $query->orderBy('id')->chunkById(100, function ($images): void {
            foreach ($images as $image) {
                ComputeImageHash::dispatch($image->id);
            }
        });
    }
}
