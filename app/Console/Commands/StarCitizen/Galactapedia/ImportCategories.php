<?php

declare(strict_types=1);

namespace App\Console\Commands\StarCitizen\Galactapedia;

use Illuminate\Console\Command;

class ImportCategories extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'galactapedia:import-categories';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import all available Galactapedia Categories.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        \App\Jobs\StarCitizen\Galactapedia\ImportCategories::dispatch();
        return 0;
    }
}
