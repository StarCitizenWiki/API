<?php

declare(strict_types=1);

namespace App\Console\Commands\CommLink;

use App\Models\Rsi\CommLink\Channel;
use App\Models\Rsi\CommLink\CommLink;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class BackfillChannelFromUrl extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'comm-link:backfill-channel
        {--fix-mismatches : Also fix records where channel_id doesn\'t match URL slug}
        {--chunk=500 : Number of comm-links to process per chunk}
        {--dry-run : Run the command without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backfill comm-link channel_id from the URL column slug.';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $fixMismatches = (bool) $this->option('fix-mismatches');
        $chunkSize = max(1, (int) $this->option('chunk'));

        $channels = Channel::query()->pluck('id', 'slug');

        $updated = 0;
        $skipped = 0;

        // Records with channel_id = 1 (Undefined)
        $this->info('Fixing undefined channels (channel_id = 1)...');

        CommLink::query()
            ->select('id', 'cig_id', 'title', 'url', 'channel_id')
            ->where('channel_id', 1)
            ->whereNotNull('url')
            ->where('url', 'LIKE', '%/comm-link/%')
            ->orderByDesc('id')
            ->chunkById($chunkSize, function (Collection $commLinks) use ($channels, $dryRun, &$updated, &$skipped): void {
                foreach ($commLinks as $commLink) {
                    $slug = $this->extractSlug($commLink->url);

                    if ($slug === null || ! $channels->has($slug)) {
                        $this->line(sprintf('  SKIP cig_id=%d: slug "%s" not in channels table', $commLink->cig_id, $slug ?? 'null'));
                        $skipped++;

                        continue;
                    }

                    if ($dryRun) {
                        $this->line(sprintf('  WOULD UPDATE cig_id=%d: channel "%s" (id=%d) - %s', $commLink->cig_id, $slug, $channels[$slug], $commLink->title));
                    } else {
                        $commLink->update(['channel_id' => $channels[$slug]]);
                    }

                    $updated++;
                }

                if (! $dryRun) {
                    $this->info(sprintf('  Processed %d comm-links...', $updated + $skipped));
                }
            });

        $this->info(sprintf('%s %d records, skipped %d.', $dryRun ? 'Would update' : 'Updated', $updated, $skipped));

        // Mismatched channels
        if ($fixMismatches) {
            $this->info('Fixing mismatched channels...');
            $mismatchUpdated = 0;
            $mismatchSkipped = 0;

            CommLink::query()
                ->select('id', 'cig_id', 'title', 'url', 'channel_id')
                ->where('channel_id', '!=', 1)
                ->whereNotNull('url')
                ->where('url', 'LIKE', '%/comm-link/%')
                ->orderByDesc('id')
                ->chunkById($chunkSize, function (Collection $commLinks) use ($channels, $dryRun, &$mismatchUpdated, &$mismatchSkipped): void {
                    foreach ($commLinks as $commLink) {
                        $slug = $this->extractSlug($commLink->url);

                        if ($slug === null || ! $channels->has($slug)) {
                            $mismatchSkipped++;

                            continue;
                        }

                        $correctChannelId = $channels[$slug];

                        if ($commLink->channel_id === $correctChannelId) {
                            continue;
                        }

                        $currentSlug = $channels->search($commLink->channel_id) ?? 'unknown';

                        if ($dryRun) {
                            $this->line(sprintf('  WOULD FIX cig_id=%d: %s (id=%d) -> %s (id=%d) - %s', $commLink->cig_id, $currentSlug, $commLink->channel_id, $slug, $correctChannelId, $commLink->title));
                        } else {
                            $commLink->update(['channel_id' => $correctChannelId]);
                        }

                        $mismatchUpdated++;
                    }

                    if (! $dryRun) {
                        $this->info(sprintf('  Checked %d comm-links...', $mismatchUpdated + $mismatchSkipped));
                    }
                });

            $this->info(sprintf('%s %d mismatches, skipped %d.', $dryRun ? 'Would fix' : 'Fixed', $mismatchUpdated, $mismatchSkipped));
        } else {
            $this->comment('Skipped. Use --fix-mismatches to also fix records where channel_id doesn\'t match URL slug.');
        }

        return self::SUCCESS;
    }

    private function extractSlug(string $url): ?string
    {
        if (preg_match('#/comm-link/([a-z0-9-]+)/#', $url, $matches) !== 1) {
            return null;
        }

        return $matches[1];
    }
}
