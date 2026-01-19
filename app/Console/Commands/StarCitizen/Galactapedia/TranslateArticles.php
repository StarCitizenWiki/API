<?php

declare(strict_types=1);

namespace App\Console\Commands\StarCitizen\Galactapedia;

use App\Jobs\StarCitizen\Galactapedia\TranslateArticle;
use App\Models\StarCitizen\Galactapedia\Article;
use App\Models\System\Language;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class TranslateArticles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'galactapedia:translate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Translate all available Galactapedia articles.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        Article::query()
            ->whereNotNull('translation->'.Language::ENGLISH)
            ->where('translation->'.Language::ENGLISH, '!=', '')
            ->chunk(100, fn (Collection $articles) => $articles->each(fn (Article $article) => TranslateArticle::dispatch($article)));

        return Command::SUCCESS;
    }
}
