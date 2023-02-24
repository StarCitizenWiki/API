<?php

declare(strict_types=1);

namespace App\Console\Commands\StarCitizenUnpacked;

use App\Jobs\StarCitizenUnpacked\Import\Items;
use App\Jobs\StarCitizenUnpacked\Import\ShopItems;
use Illuminate\Console\Command;

class ImportShopItems extends Command
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
    protected $description = 'Import Shops and Items';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Items::dispatch();
        ShopItems::dispatch();
        return 0;
    }
}
