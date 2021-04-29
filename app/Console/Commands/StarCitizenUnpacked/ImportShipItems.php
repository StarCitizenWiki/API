<?php

declare(strict_types=1);

namespace App\Console\Commands\StarCitizenUnpacked;

use App\Jobs\StarCitizenUnpacked\Import\ShipItems;
use Illuminate\Console\Command;

class ImportShipItems extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'unpacked:import-ship-items';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import ship items from scunpacked';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        ShipItems::dispatch();
        return 0;
    }
}
