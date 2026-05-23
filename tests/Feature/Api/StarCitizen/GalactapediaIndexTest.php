<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\StarCitizen\Galactapedia\Article;
use App\Models\StarCitizen\Galactapedia\Category;
use App\Models\StarCitizen\Galactapedia\Tag;
use App\Models\StarCitizen\Galactapedia\Template;
use App\Models\System\Language;

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

it('returns article relations on the index response', function (): void {
    $article = Article::factory()->create([
        'title' => 'ArcCorp Overview',
        'slug' => 'arccorp-overview',
        'translation' => [
            Language::ENGLISH => 'ArcCorp is a city planet.',
            Language::GERMAN => 'ArcCorp ist ein Stadtplanet.',
        ],
    ]);

    $category = Category::factory()->create(['name' => 'Lore']);
    $tag = Tag::factory()->create(['name' => 'Planet']);
    $template = Template::factory()->create(['template' => 'location']);

    $article->categories()->attach($category);
    $article->tags()->attach($tag);
    $article->templates()->attach($template);
    $article->update([
        'categories_count' => 1,
        'tags_count' => 1,
        'templates_count' => 1,
    ]);

    $response = $this->getJson(route('galactapedia.index', [
        'page[size]' => 1,
    ]));

    $response->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', (string) $article->cig_id)
        ->assertJsonPath('data.0.title', 'ArcCorp Overview')
        ->assertJsonPath('data.0.template', 'location')
        ->assertJsonPath('data.0.category', 'Lore')
        ->assertJsonPath('data.0.tag', 'Planet')
        ->assertJsonPath('data.0.categories.0.name', 'Lore')
        ->assertJsonPath('data.0.tags.0.name', 'Planet')
        ->assertJsonPath('meta.current_page', 1)
        ->assertJsonPath('meta.per_page', 1)
        ->assertJsonPath('meta.total', 1);
});

it('orders articles by newest first', function (): void {
    $olderArticle = Article::factory()->create([
        'title' => 'Older Article',
        'slug' => 'older-article',
    ]);

    $newerArticle = Article::factory()->create([
        'title' => 'Newer Article',
        'slug' => 'newer-article',
    ]);

    $response = $this->getJson(route('galactapedia.index', [
        'page[size]' => 2,
    ]));

    $response->assertSuccessful()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.id', (string) $newerArticle->cig_id)
        ->assertJsonPath('data.0.title', 'Newer Article')
        ->assertJsonPath('data.1.id', (string) $olderArticle->cig_id)
        ->assertJsonPath('data.1.title', 'Older Article')
        ->assertJsonPath('meta.current_page', 1)
        ->assertJsonPath('meta.per_page', 2)
        ->assertJsonPath('meta.total', 2);
});
