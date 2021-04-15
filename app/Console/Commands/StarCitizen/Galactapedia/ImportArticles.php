<?php

declare(strict_types=1);

namespace App\Console\Commands\StarCitizen\Galactapedia;

use App\Console\Commands\AbstractQueueCommand;
use App\Jobs\StarCitizen\Galactapedia\ImportArticlesFromCategory;
use App\Models\StarCitizen\Galactapedia\Category;

class ImportArticles extends AbstractQueueCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'galactapedia:import-articles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import all articles.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        \App\Jobs\StarCitizen\Galactapedia\ImportArticles::dispatch();
        return 0;
    }
}
