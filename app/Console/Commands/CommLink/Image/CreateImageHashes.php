<?php

declare(strict_types=1);

namespace App\Console\Commands\CommLink\Image;

use App\Console\Commands\AbstractQueueCommand as QueueCommand;
use App\Jobs\Rsi\CommLink\Image\CreateImageHash;
use App\Models\Rsi\CommLink\Image\Image;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class CreateImageHashes extends QueueCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'comm-links:images-create-hashes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create Image hashes for all Comm-Links images';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $this->info('Starting calculation of image hashes');

        $images = $this->getImages();

        $this->createProgressBar($images->count());

        $images->chunk(
            100,
            function (Collection $images) {
                $images->each(
                    function (Image $image) {
                        dispatch(new CreateImageHash($image));
                        $this->advanceBar();
                    }
                );
            }
        );

        $this->finishBar();

        return 0;
    }

    /**
     * The images to create hashes for
     * Image needs to have an attached comm link and metadata
     *
     * @return Builder
     */
    private function getImages(): Builder
    {
        return Image::query()
            ->whereHas('commLinks')
            ->whereDoesntHave('hash')
            ->whereHas(
                'metadata',
                function (Builder $query) {
                    $query->where('size', '<', 1024 * 1024 * 10); // Max 10MB files
                }
            )
            ->where(
                function (Builder $query) {
                    $query->orWhereRaw('LOWER(src) LIKE \'%.jpg\'')
                        ->orWhereRaw('LOWER(src) LIKE \'%.jpeg\'')
                        ->orWhereRaw('LOWER(src) LIKE \'%.png\'')
                        ->orWhereRaw('LOWER(src) LIKE \'%.webp\'');
                }
            );
    }
}
