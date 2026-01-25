<?php

declare(strict_types=1);

namespace App\Console\Commands\CommLink;

use App\Jobs\Rsi\CommLink\Image\ComputeSimilarImageIds as ComputeSimilarImageIdsJob;
use App\Models\Rsi\CommLink\Image\Image;
use Illuminate\Console\Command;

class ComputeSimilarImageIds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'comm-link:compute-similar-image-ids
        {--queue=expensive : Queue name for hashing jobs}
        {--recent : Only compute for images created in the last week}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Compute and mark similar/duplicate comm-link images.';

    public function handle(): int
    {
        $recentOnly = (bool) $this->option('recent');
        $queue = (string) $this->option('queue');

        $query = Image::query()
            ->select('id', 'base_image_id', 'created_at')
            ->whereNull('base_image_id')
            ->whereHas('hash', function ($q): void {
                $q->whereNotNull('pdq_hash');
            });

        if ($recentOnly) {
            $query->where('created_at', '>=', now()->subWeek());
        }

        $dispatched = 0;

        $query->orderBy('created_at')->chunk(25, function ($images) use (&$dispatched, $queue): void {
            foreach ($images as $image) {
                // Refresh to check if another job already set base_image_id
                $image->refresh();

                if ($image->base_image_id !== null) {
                    continue;
                }

                ComputeSimilarImageIdsJob::dispatch($image->id)
                    ->onConnection('database')
                    ->onQueue($queue);
                $dispatched++;
            }
        });

        $this->info(sprintf('Dispatched %d similarity computation jobs to queue "%s".', $dispatched, $queue));

        return self::SUCCESS;
    }
}
