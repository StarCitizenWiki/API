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
     * @var int Offset to start parsing from
     */
    private $offset;

    /**
     * Create a new job instance.
     *
     * @param int $offset Directory Offset
     */
    public function __construct(int $offset = 0)
    {
        $this->offset = $offset;
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
                    $query->where('cig_id', '>=', $this->offset);
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
