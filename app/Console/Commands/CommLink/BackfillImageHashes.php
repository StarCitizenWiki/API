<?php

declare(strict_types=1);

namespace App\Console\Commands\CommLink;

use App\Jobs\Rsi\CommLink\Image\ComputeImageHash;
use App\Models\Rsi\CommLink\Image\Image;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

class BackfillImageHashes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'comm-link:backfill-image-hashes
        {--chunk=500 : Number of images to process per chunk}
        {--queue=expensive : Queue name for hashing jobs}
        {--all : Include images that already have hashes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatch comm-link image hashing jobs.';

    public function handle(): int
    {
        $chunkSize = max(1, (int) $this->option('chunk'));
        $queue = (string) $this->option('queue');
        $includeAll = (bool) $this->option('all');

        $query = Image::query()
            ->where(function (Builder $query) {
                $query->whereRelation('metadata', 'mime', 'LIKE', 'video%')
                    ->orWhereRelation('metadata', 'mime', 'LIKE', 'image%');
            })
            ->where('src', 'NOT LIKE', '%.svg')
            ->where('src', 'NOT LIKE', '%.tiff')
            ->select('id');

        if (! $includeAll) {
            $query->whereDoesntHave('hash');
        }

        $dispatched = 0;

        $query->orderByDesc('id')->chunkById($chunkSize, function ($images) use (&$dispatched, $queue): void {
            foreach ($images as $image) {
                ComputeImageHash::dispatch($image->id)
                    ->onConnection('database')
                    ->onQueue($queue);
                $dispatched++;
            }
        });

        $this->info(sprintf('Dispatched %d hashing jobs to queue "%s".', $dispatched, $queue));

        return self::SUCCESS;
    }
}
