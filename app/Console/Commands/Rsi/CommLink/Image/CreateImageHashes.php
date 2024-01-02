<?php

declare(strict_types=1);

namespace App\Console\Commands\Rsi\CommLink\Image;

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
                        CreateImageHash::dispatch($image)->onQueue('comm_link_images');
                        $this->advanceBar();
                    }
                );
            }
        );

        $this->finishBar();

        return QueueCommand::SUCCESS;
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
            ->where(function (Builder $query) {
                $query->whereRelation('metadata', 'mime', 'LIKE', 'video%')
                    ->orWhereRelation('metadata', 'mime', 'LIKE', 'image%');
            })
            ->whereHas('commLinks')
            ->doesntHave('hash')
            ->where('src', 'NOT LIKE', '%.svg')
            ->where('src', 'NOT LIKE', '%.tiff');
    }
}
