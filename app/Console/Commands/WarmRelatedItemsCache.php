<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Game\GameVersion;
use App\Models\Game\Item;
use App\Services\RelatedItemsBuilder;
use Illuminate\Console\Command;

class WarmRelatedItemsCache extends Command
{
    protected $signature = 'cache:warm-related-items {--limit=100 : Number of items to warm cache for}';

    protected $description = 'Warm up related items cache for popular items';

    public function handle(): void
    {
        $limit = (int) $this->option('limit');

        $gameVersion = GameVersion::where('is_default', true)->first();

        if ($gameVersion === null) {
            $this->error('No default game version found.');

            return;
        }

        $items = Item::query()
            ->orderBy('id')
            ->limit($limit)
            ->get();

        $this->info("Warming cache for {$items->count()} items in version {$gameVersion->code}...");

        foreach ($items as $item) {
            $builder = new RelatedItemsBuilder($gameVersion->code);
            $builder->build($item);

            $this->line("Warmed: {$item->uuid}");
        }

        $this->info('Cache warming complete!');
    }
}
