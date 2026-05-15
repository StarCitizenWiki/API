<?php

declare(strict_types=1);

namespace App\Console\Commands\StarCitizen\Vehicle;

use App\Jobs\StarCitizen\Vehicle\ImportPledgeStore as ImportPledgeStoreJob;
use Illuminate\Console\Command;

class ImportPledgeStore extends Command
{
    protected $signature = 'pledge-store:import';

    protected $description = 'Import all pledge store SKUs from the RSI GraphQL API';

    public function handle(): int
    {
        $this->info('Dispatching pledge store import...');

        ImportPledgeStoreJob::dispatch();

        $this->info('Pledge store import dispatched.');

        return self::SUCCESS;
    }
}
