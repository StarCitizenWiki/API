<?php

declare(strict_types=1);

namespace App\Console\Commands\StarCitizen\Galactapedia;

use App\Jobs\StarCitizen\Galactapedia\TranslateArticle;
use App\Models\StarCitizen\Galactapedia\Article;
use App\Models\System\Language;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
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
            ->whereHas(
                'translations',
                function (Builder $query) {
                    $query
                        ->where('locale_code', Language::ENGLISH)
                        ->whereRaw("translation <> ''");
                }
            )
            ->chunk(
                100,
                function (Collection $articles) {
                    $articles->each(
                        function (Article $article) {
                            TranslateArticle::dispatch($article);
                        }
                    );
                }
            );

        return 0;
    }
}
