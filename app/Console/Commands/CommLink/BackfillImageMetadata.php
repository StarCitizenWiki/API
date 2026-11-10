<?php

declare(strict_types=1);

namespace App\Console\Commands\CommLink;

use App\Jobs\Rsi\CommLink\Image\CreateImageMetadatum;
use App\Models\Rsi\CommLink\Image\Image;
use Illuminate\Console\Command;

class BackfillImageMetadata extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'comm-link:backfill-image-metadata
        {--chunk=500 : Number of images to process per chunk}
        {--queue=default : Queue name for metadata jobs}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatch comm-link image metadata jobs.';

    public function handle(): int
    {
        $chunkSize = max(1, (int) $this->option('chunk'));
        $queue = (string) $this->option('queue');

        $query = Image::query()
            ->whereHas('commLinks')
            ->missingOrUndefinedMetadata()
            ->select('id');

        $dispatched = 0;

        $query->orderBy('id')->chunkById($chunkSize, function ($images) use (&$dispatched, $queue): void {
            foreach ($images as $image) {
                CreateImageMetadatum::dispatch($image->id)->onQueue($queue);
                $dispatched++;
            }
        });

        $this->info(sprintf('Dispatched %d metadata jobs to queue "%s".', $dispatched, $queue));

        return self::SUCCESS;
    }
}
