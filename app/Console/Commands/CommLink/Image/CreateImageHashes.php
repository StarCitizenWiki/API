<?php declare(strict_types=1);

namespace App\Console\Commands\CommLink\Image;

use App\Jobs\Rsi\CommLink\Image\CreateImageHash;
use App\Models\Rsi\CommLink\Image\Image;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class CreateImageHashes extends Command
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

        $query = Image::query()
            ->whereHas('commLinks')
            ->whereDoesntHave('hash')
            ->whereHas('metadata', function (Builder $query) {
                $query->where('size', '<', 1024 * 1024 * 10); // Max 10MB files
            })
            ->where(
                function (Builder $query) {
                    $query->orWhereRaw('LOWER(src) LIKE \'%.jpg\'')
                        ->orWhereRaw('LOWER(src) LIKE \'%.jpeg\'')
                        ->orWhereRaw('LOWER(src) LIKE \'%.png\'')
                        ->orWhereRaw('LOWER(src) LIKE \'%.webp\'');
                }
            );

        $bar = $this->output->createProgressBar($query->count());

        $query->chunk(
            100,
            function (Collection $images) use ($bar) {
                $images->each(
                    function (Image $image) use ($bar) {
                        dispatch(new CreateImageHash($image));
                        $bar->advance();
                    }
                );
            }
        );

        $bar->finish();

        return 0;
    }
}
