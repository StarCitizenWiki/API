<?php

declare(strict_types=1);

namespace App\Console\Commands\StarCitizen\Galactapedia;

use App\Console\Commands\AbstractQueueCommand;
use App\Jobs\StarCitizen\Galactapedia\ImportArticleProperty;
use App\Models\StarCitizen\Galactapedia\Article;

class ImportArticleProperties extends AbstractQueueCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'galactapedia:import-properties';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import properties of all available articles.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $articles = Article::query()->with('templates')->get();

        $this->createProgressBar($articles->count());

        $articles
            ->each(function (Article $article) {
                ImportArticleProperty::dispatch($article);
                $this->advanceBar();
            });

        return 0;
    }
}
