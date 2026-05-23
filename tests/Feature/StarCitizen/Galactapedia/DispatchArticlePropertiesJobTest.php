<?php

declare(strict_types=1);

use App\Jobs\StarCitizen\Galactapedia\ImportArticleProperty;
use App\Jobs\StarCitizen\Galactapedia\Sync\DispatchArticleProperties;
use App\Models\StarCitizen\Galactapedia\Article;
use Illuminate\Support\Facades\Bus;

it('dispatches property jobs for each article', function (): void {
    Bus::fake();

    $first = Article::query()->create([
        'cig_id' => 'a-1',
        'title' => 'Alpha',
        'slug' => 'alpha',
    ]);

    $second = Article::query()->create([
        'cig_id' => 'a-2',
        'title' => 'Beta',
        'slug' => 'beta',
    ]);

    $job = new DispatchArticleProperties;
    $job->handle();

    Bus::assertDispatchedTimes(ImportArticleProperty::class, 2);

    Bus::assertDispatched(ImportArticleProperty::class, function (ImportArticleProperty $job) use ($first): bool {
        return $job->articleId === $first->id;
    });

    Bus::assertDispatched(ImportArticleProperty::class, function (ImportArticleProperty $job) use ($second): bool {
        return $job->articleId === $second->id;
    });
});
