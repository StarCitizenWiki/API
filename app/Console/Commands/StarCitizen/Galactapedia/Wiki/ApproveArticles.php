<?php

declare(strict_types=1);

namespace App\Console\Commands\StarCitizen\Galactapedia\Wiki;

use App\Jobs\Wiki\ApproveRevisions;
use App\Models\StarCitizen\Galactapedia\Article;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class ApproveArticles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'galactapedia:approve-wiki-pages';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Approve Galactapedia Wiki Pages';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if (config('services.wiki_approve_revs.access_secret') === null) {
            return 1;
        }

        Article::query()
            ->get()
            ->map(function (Article $article) {
                return $article->title;
            })
            ->chunk(25)
            ->each(function (Collection $chunk) {
                dispatch(new ApproveRevisions($chunk->toArray(), false, true));
            });

        return 0;
    }
}
