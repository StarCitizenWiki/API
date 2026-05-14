<?php

declare(strict_types=1);

namespace App\Console\Commands\Game;

use App\Models\Game\ItemData;
use App\Services\ItemRelevanceChecker;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillPlayerRelevant extends Command
{
    protected $signature = 'game:backfill-player-relevant {--chunk=500}';

    protected $description = 'Backfill the is_player_relevant flag on existing game_item_data rows';

    public function handle(): int
    {
        $chunkSize = (int) $this->option('chunk');

        $total = ItemData::query()->count();
        $this->info("Processing {$total} rows in chunks of {$chunkSize}...");

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $updated = 0;

        ItemData::query()
            ->select(['id', 'name', 'class_name', 'is_player_relevant'])
            ->chunkById($chunkSize, function ($items) use (&$updated, $bar): void {
                $changes = [];

                foreach ($items as $item) {
                    $expected = ItemRelevanceChecker::isPlayerRelevant($item->name, $item->class_name);

                    if ($item->is_player_relevant !== $expected) {
                        $changes[$item->id] = $expected;
                    }
                }

                if ($changes !== []) {
                    foreach ($changes as $id => $value) {
                        DB::table('game_item_data')
                            ->where('id', $id)
                            ->update(['is_player_relevant' => $value]);
                    }

                    $updated += count($changes);
                }

                $bar->advance(count($items));
            });

        $bar->finish();
        $this->newLine();
        $this->info("Done. Updated {$updated} rows.");

        return self::SUCCESS;
    }
}
