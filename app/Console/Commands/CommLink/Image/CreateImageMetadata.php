<?php declare(strict_types=1);

namespace App\Console\Commands\CommLink\Image;

use App\Models\Rsi\CommLink\Image\Image;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class CreateImageMetadata extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'comm-links:create-image-metadata';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Requests image metadata for all downloaded Comm-Links';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $this->info('Creating Image Metadata');

        $query = Image::query()
            ->whereHas('commLinks')
            ->whereDoesntHave('metadata');

        $bar = $this->output->createProgressBar($query->count());

        $query->chunk(
            100,
            function (Collection $images) use ($bar) {
                $images->each(
                    function (Image $image) use ($bar) {
                        dispatch(new \App\Jobs\Rsi\CommLink\Image\CreateImageMetadata($image));
                        $bar->advance();
                    }
                );
            }
        );

        $bar->finish();

        return 0;
    }
}
