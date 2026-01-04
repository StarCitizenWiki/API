<?php

declare(strict_types=1);

namespace App\Console\Commands\StarCitizen\Galactapedia;

use App\Jobs\StarCitizen\Galactapedia\Sync\SyncGalactapedia as SyncGalactapediaJob;
use App\Support\Filters\FilterCache;
use Illuminate\Console\Command;

class SyncGalactapedia extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'galactapedia:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Galactapedia categories, articles, and properties.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Dispatching Galactapedia Sync');

        SyncGalactapediaJob::dispatch();
        FilterCache::bust(FilterCache::NAMESPACE_GALACTAPEDIA);

        return Command::SUCCESS;
    }
}
