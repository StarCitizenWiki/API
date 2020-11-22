<?php

declare(strict_types=1);

namespace App\Jobs\Rsi\CommLink\Image;

use App\Models\Rsi\CommLink\Image\Image;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class CreateImageMetadata implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @var int Comm-Link IDs to operate on
     */
    private $commLinkIds;

    /**
     * Create a new job instance.
     *
     * @param array $commLinkIds
     */
    public function __construct(array $commLinkIds = [])
    {
        $this->commLinkIds = $commLinkIds;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        $query = Image::query()
            ->whereHas(
                'commLinks',
                function (Builder $query) {
                    $query->whereIn('cig_id', $this->commLinkIds);
                }
            )
            ->whereDoesntHave('metadata');

        $query->chunk(
            100,
            function (Collection $images) {
                $images->each(
                    function (Image $image) {
                        dispatch(new CreateImageMetadatum($image));
                    }
                );
            }
        );
    }
}
