<?php

namespace App\Console\Commands\StarCitizenUnpacked;

use App\Console\Commands\AbstractQueueCommand;
use App\Jobs\StarCitizenUnpacked\TranslateItem;
use App\Models\StarCitizenUnpacked\Item;

class TranslateItems extends AbstractQueueCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'unpacked:translate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Translate all item descriptions';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Translating Items');
        $items = Item::query()->whereHas('translation');
        $this->createProgressBar($items->count());

        $items->each(function (Item $item) {
            TranslateItem::dispatch($item);
            $this->advanceBar();
        });

        return 0;
    }
}
