<?php

namespace App\Console\Commands\Rsi\CommLink\Image;

use App\Models\Rsi\CommLink\Image\Image;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class ComputeSimilarImageIds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'comm-links:compute-similar-image-ids';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        Image::query()
            ->whereNull('base_image_id')
            //->whereRelation('metadata', 'size', '>=', 250 * 1024)
            ->with([
                'metadata' => fn($query) => $query->orderBy('size', 'DESC'),
            ])
            ->orderBy('created_at')
            ->chunk(25, function (Collection $images) {
                $images->each(function (Image $image) {
                    $image->refresh();

                    if ($image->base_image_id !== null) {
                        return;
                    }

                    \App\Jobs\Rsi\CommLink\Image\ComputeSimilarImageIds::dispatch($image)->onQueue('comm_link_images');
                });
            });

        return Command::SUCCESS;
    }
}
