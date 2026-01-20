<?php

declare(strict_types=1);

namespace App\Console\Commands\CommLink;

use App\Models\Rsi\CommLink\CommLink;
use Illuminate\Console\Command;

class BackfillCommLinkCounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'comm-link:backfill-counts
        {--chunk=500 : Number of comm-links to process per chunk}
        {--dry-run : Run the command without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backfill comm-link images_count and links_count from pivot tables.';

    public function handle(): int
    {
        $chunkSize = max(1, (int) $this->option('chunk'));
        $dryRun = (bool) $this->option('dry-run');

        $query = CommLink::query()->select('id');

        $updated = 0;

        $query->orderByDesc('id')->chunkById($chunkSize, function ($commLinks) use (&$updated, $dryRun): void {
            foreach ($commLinks as $commLink) {
                $imagesCount = $commLink->images()->count();
                $linksCount = $commLink->links()->count();

                if ($dryRun) {
                    $this->line(sprintf('Comm-Link ID %d: images=%d, links=%d', $commLink->id, $imagesCount, $linksCount));
                } else {
                    $commLink->update([
                        'images_count' => $imagesCount,
                        'links_count' => $linksCount,
                    ]);
                }

                $updated++;
            }

            if (! $dryRun) {
                $this->info(sprintf('Processed %d comm-links...', $updated));
            }
        });

        if ($dryRun) {
            $this->info(sprintf('Dry run completed for %d comm-links.', $updated));
        } else {
            $this->info(sprintf('Successfully updated %d comm-links.', $updated));
        }

        return self::SUCCESS;
    }
}
