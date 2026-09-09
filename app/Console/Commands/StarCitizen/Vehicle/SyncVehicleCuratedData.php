<?php

declare(strict_types=1);

namespace App\Console\Commands\StarCitizen\Vehicle;

use App\Jobs\StarCitizen\Vehicle\SyncVehicleCuratedData as SyncVehicleCuratedDataJob;
use Illuminate\Console\Command;

class SyncVehicleCuratedData extends Command
{
    protected $signature = 'vehicles:sync-curated-data';

    protected $description = 'Sync vehicle curated data from the starcitizen.tools wiki infoboxes';

    public function handle(): int
    {
        $this->info('Dispatching vehicle curated data sync...');

        SyncVehicleCuratedDataJob::dispatch();

        $this->info('Vehicle curated data sync dispatched.');

        return self::SUCCESS;
    }
}
