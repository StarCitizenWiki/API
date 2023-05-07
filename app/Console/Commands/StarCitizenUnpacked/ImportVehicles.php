<?php

declare(strict_types=1);

namespace App\Console\Commands\StarCitizenUnpacked;

use App\Jobs\SC\Import\Vehicle;
use Illuminate\Console\Command;

class ImportVehicles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'unpacked:import-vehicles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import vehicles from scunpacked';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Vehicle::dispatch();
        return 0;
    }
}
