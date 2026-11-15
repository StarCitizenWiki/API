<?php

declare(strict_types=1);

use App\Jobs\StarCitizen\Galactapedia\ImportArticleProperty;
use App\Models\StarCitizen\Galactapedia\Article;
use App\Models\StarCitizen\Galactapedia\Template;
use App\Services\GalactapediaClient;
use App\Services\RsiDownloadClient;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

it('passes the article id as a variable when importing properties', function (): void {
    $id = 'id" } injected';
    $article = Article::create(['cig_id' => $id, 'title' => 'Test', 'slug' => 'test']);
    $article->templates()->attach(Template::create(['template' => 'Planet']));

    Http::fakeSequence()
        ->push(['data' => ['template' => ['fields' => [['name' => 'description']]]]])
        ->push(['data' => ['Article' => ['template' => [['Planet' => ['description' => 'Example']]]]]]);

    (new ImportArticleProperty($article->id))->handle(new GalactapediaClient(new RsiDownloadClient));

    Http::assertSent(fn (Request $request): bool => ($request['variables']['id'] ?? null) === $id
        && ! str_contains($request['query'], $id)
        && str_contains($request['query'], 'Article(id: $id)'));
    expect($article->properties()->where('name', 'description')->where('content', 'Example')->exists())->toBeTrue();
});
