<?php

declare(strict_types=1);

namespace App\Jobs\StarCitizen\Galactapedia\Sync;

use App\Jobs\StarCitizen\Galactapedia\ImportArticleProperty;
use App\Models\StarCitizen\Galactapedia\Article;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Queue\Queueable;

class DispatchArticleProperties implements ShouldQueue
{
    use Queueable;

    private const CHUNK_SIZE = 100;

    public int $timeout = 120;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Article::query()
            ->select('id')
            ->chunkById(self::CHUNK_SIZE, function (Collection $articles): void {
                $articles->each(function (Article $article): void {
                    ImportArticleProperty::dispatch($article->id);
                });
            });
    }
}
