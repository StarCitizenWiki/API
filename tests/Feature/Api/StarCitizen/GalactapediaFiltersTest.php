<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\StarCitizen\Galactapedia\Article;
use App\Models\StarCitizen\Galactapedia\Category;
use App\Models\StarCitizen\Galactapedia\Tag;
use App\Models\StarCitizen\Galactapedia\Template;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    app()->instance('env', 'production');
    app('cache')->setDefaultDriver('array');
    app()->forgetInstance('cache');
    app('cache')->forgetDriver(['array', 'database']);
    Cache::store('array')->flush();
});

it('returns galactapedia filter values with counts', function (): void {
    GameVersion::factory()->create([
        'code' => '3.25.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $category = Category::factory()->create(['name' => 'Lore']);
    $tag = Tag::factory()->create(['name' => 'Banu']);
    $template = Template::factory()->create(['template' => 'species']);

    $article = Article::factory()->create();
    $article->categories()->attach($category);
    $article->update(['categories_count' => 1]);
    $article->tags()->attach($tag);
    $article->update(['tags_count' => 1]);
    $article->templates()->attach($template);
    $article->update(['templates_count' => 1]);

    Article::factory()->create();

    $this->getJson(route('galactapedia.filters'))
        ->assertOk()
        ->assertExactJson([
            'filters' => [
                'category' => [
                    ['value' => 'Lore', 'label' => 'Lore', 'count' => 1],
                    ['value' => null, 'label' => 'Unknown', 'count' => 1],
                ],
                'tag' => [
                    ['value' => 'Banu', 'label' => 'Banu', 'count' => 1],
                    ['value' => null, 'label' => 'Unknown', 'count' => 1],
                ],
                'template' => [
                    ['value' => 'species', 'label' => 'species', 'count' => 1],
                    ['value' => null, 'label' => 'Unknown', 'count' => 1],
                ],
            ],
        ]);
});

it('returns filtered galactapedia facet values without caching the filtered response', function (): void {
    $lore = Category::factory()->create(['name' => 'Lore']);
    $history = Category::factory()->create(['name' => 'History']);
    $banu = Tag::factory()->create(['name' => 'Banu']);
    $human = Tag::factory()->create(['name' => 'Human']);
    $species = Template::factory()->create(['template' => 'species']);
    $timeline = Template::factory()->create(['template' => 'timeline']);

    $matching = Article::factory()->create();
    $matching->categories()->attach($lore);
    $matching->tags()->attach($banu);
    $matching->templates()->attach($species);

    $nonMatching = Article::factory()->create();
    $nonMatching->categories()->attach($history);
    $nonMatching->tags()->attach($human);
    $nonMatching->templates()->attach($timeline);

    $response = $this->getJson(route('galactapedia.filters', [
        'filter' => ['category' => 'Lore'],
    ]));

    $response->assertOk()
        ->assertExactJson([
            'filters' => [
                'category' => [
                    ['value' => 'Lore', 'label' => 'Lore', 'count' => 1],
                ],
                'tag' => [
                    ['value' => 'Banu', 'label' => 'Banu', 'count' => 1],
                ],
                'template' => [
                    ['value' => 'species', 'label' => 'species', 'count' => 1],
                ],
            ],
        ]);

    expect(Cache::get('filters:index:galactapedia'))->toBeNull();
});
