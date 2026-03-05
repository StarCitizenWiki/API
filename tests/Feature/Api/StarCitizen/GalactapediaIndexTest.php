<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\StarCitizen\Galactapedia\Article;
use App\Models\StarCitizen\Galactapedia\Category;
use App\Models\StarCitizen\Galactapedia\Tag;
use App\Models\StarCitizen\Galactapedia\Template;
use App\Models\System\Language;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    GameVersion::factory()->create([
        'code' => '3.25.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    Language::factory()->create(['code' => Language::ENGLISH]);
    Language::factory()->create(['code' => Language::GERMAN]);
});

it('avoids repeated relation and language queries on galactapedia index', function (): void {
    $articles = Article::factory()->count(12)->create([
        'translation' => [
            Language::ENGLISH => 'ArcCorp is a city planet.',
            Language::GERMAN => 'ArcCorp ist ein Stadtplanet.',
        ],
    ]);

    $category = Category::factory()->create(['name' => 'Lore']);
    $tag = Tag::factory()->create(['name' => 'Planet']);
    $template = Template::factory()->create(['template' => 'location']);

    $articles->each(function (Article $article) use ($category, $tag, $template): void {
        $article->categories()->attach($category);
        $article->tags()->attach($tag);
        $article->templates()->attach($template);
        $article->update([
            'categories_count' => 1,
            'tags_count' => 1,
            'templates_count' => 1,
        ]);
    });

    DB::flushQueryLog();
    DB::enableQueryLog();

    $response = $this->getJson(route('galactapedia.index', [
        'page[size]' => 12,
    ]));

    DB::disableQueryLog();

    $response->assertSuccessful()
        ->assertJsonCount(12, 'data');

    $queries = collect(DB::getQueryLog())
        ->pluck('query')
        ->map(static fn (string $query): string => strtolower($query));

    $templateQueries = $queries
        ->filter(static fn (string $query): bool => str_contains($query, 'from "galactapedia_templates" inner join "galactapedia_article_templates"'))
        ->count();

    $categoryQueries = $queries
        ->filter(static fn (string $query): bool => str_contains($query, 'from "galactapedia_categories" inner join "galactapedia_article_categories"'))
        ->count();

    $tagQueries = $queries
        ->filter(static fn (string $query): bool => str_contains($query, 'from "galactapedia_tags" inner join "galactapedia_article_tags"'))
        ->count();

    $languageQueries = $queries
        ->filter(static fn (string $query): bool => str_contains($query, 'select "code" from "languages"'))
        ->count();

    expect($templateQueries)->toBe(1)
        ->and($categoryQueries)->toBe(1)
        ->and($tagQueries)->toBe(1)
        ->and($languageQueries)->toBe(1)
        ->and($queries->count())->toBeLessThanOrEqual(20);
});
