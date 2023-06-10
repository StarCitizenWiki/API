<?php

declare(strict_types=1);

namespace App\Console\Commands\SC;

use App\Console\Commands\AbstractQueueCommand;
use App\Jobs\SC\Import\ShopItems;
use Illuminate\Console\Command;

class ImportShops extends AbstractQueueCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sc:import-shops';

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
