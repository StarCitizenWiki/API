<?php

declare(strict_types=1);

namespace App\Console\Commands\Galactapedia;

use App\Console\Commands\AbstractQueueCommand;
use App\Jobs\Api\StarCitizen\Galactapedia\ImportArticlesFromCategory;
use App\Models\Api\StarCitizen\Galactapedia\Category;

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
    protected $description = 'Import articles from all imported categories.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $categories = Category::all();

        $this->createProgressBar($categories->count());

        $categories
            ->each(function (Category $category) {
                $this->bar->advance();
                ImportArticlesFromCategory::dispatch($category);
            });

        return 0;
    }
}
