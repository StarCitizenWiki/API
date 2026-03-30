<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\StarCitizen\Galactapedia\Article;
use App\Models\StarCitizen\Galactapedia\Category;
use App\Models\StarCitizen\Galactapedia\Tag;
use App\Models\StarCitizen\Galactapedia\Template;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

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
