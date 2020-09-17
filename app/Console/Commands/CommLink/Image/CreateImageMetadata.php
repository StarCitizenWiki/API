<?php declare(strict_types=1);

namespace App\Console\Commands\CommLink\Image;

use App\Console\Commands\AbstractQueueCommand as QueueCommand;
use App\Jobs\Rsi\CommLink\Image\CreateImageMetadatum;
use App\Models\Rsi\CommLink\Image\Image;
use Illuminate\Support\Collection;

class CreateImageMetadata extends QueueCommand
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

        $this->createProgressBar($query->count());

        $query->chunk(
            100,
            function (Collection $images) {
                $images->each(
                    function (Image $image) {
                        dispatch(new CreateImageMetadatum($image));
                        $this->advanceBar();
                    }
                );
            }
        );

        $this->finishBar();

        return 0;
    }
}
