<?php declare(strict_types=1);

namespace App\Console\Commands\CommLink\Image;

use App\Jobs\Rsi\CommLink\Image\CreateImageMetadatum;
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
    protected $signature = 'comm-links:images-create-metadata';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Request image metadata for all Comm-Link images';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $this->info('Starting creation of image metadata.');

        $query = Image::query()
            ->whereHas('commLinks')
            ->whereDoesntHave('metadata');

        $bar = $this->output->createProgressBar($query->count());

        $query->chunk(
            100,
            function (Collection $images) use ($bar) {
                $images->each(
                    function (Image $image) use ($bar) {
                        dispatch(new CreateImageMetadatum($image));
                        $bar->advance();
                    }
                );
            }
        );

        $bar->finish();

        return 0;
    }
}
