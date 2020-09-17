<?php declare(strict_types=1);

namespace App\Jobs\Rsi\CommLink\Image;

use App\Jobs\AbstractBaseDownloadData as BaseDownloadData;
use App\Models\Rsi\CommLink\Image\Image;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class CreateImageHashes extends BaseDownloadData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
        if (!extension_loaded('gd') && !extension_loaded('imagick')) {
            app('Log')::error('Required extension "GD" or "Imagick" not available.');

            $this->fail('Required extension "GD" or "Imagick" not available.');
        }

        $query = Image::query()
            ->whereHas(
                'commLinks',
                function (Builder $query) {
                    $query->where('cig_id', '>=', $this->offset);
                }
            )
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

        $query->chunk(
            100,
            function (Collection $images) {
                $images->each(
                    function (Image $image) {
                        dispatch(new CreateImageHash($image));
                    }
                );
            }
        );
    }
}
