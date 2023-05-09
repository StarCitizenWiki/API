<?php

declare(strict_types=1);

namespace App\Console\Commands\StarCitizenUnpacked;

use App\Console\Commands\AbstractQueueCommand;
use App\Jobs\SC\Import\ShopItems;
use Illuminate\Console\Command;

class ImportShopItems extends AbstractQueueCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'unpacked:import-shop-items';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import Shops and their Items';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $this->info('Importing Shops');
        ShopItems::dispatch();
        $this->info('Done');
        return Command::SUCCESS;
    }
}
