<?php

declare(strict_types=1);

use App\Models\StarCitizen\Galactapedia\Article;
use App\Models\StarCitizen\Galactapedia\ArticleProperty;
use App\Models\StarCitizen\Galactapedia\Category;
use App\Models\StarCitizen\Galactapedia\Tag;
use App\Models\StarCitizen\Galactapedia\Template;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders the galactapedia show view with api data', function (): void {
    $article = Article::factory()->create([
        'cig_id' => '1234567890',
        'title' => 'ArcCorp',
        'slug' => 'arccorp',
        'translation' => ['en' => 'ArcCorp is a vast city planet.'],
    ]);

    $relatedArticle = Article::factory()->create([
        'cig_id' => '1234567891',
        'title' => 'Stanton',
        'slug' => 'stanton',
        'translation' => ['en' => 'Stanton is a bustling system.'],
    ]);

    $category = Category::factory()->create([
        'name' => 'Locations',
    ]);

    $tag = Tag::factory()->create([
        'name' => 'Planet',
    ]);

    $template = Template::factory()->create([
        'template' => 'Location',
    ]);

    $article->categories()->attach($category->id);
    $article->tags()->attach($tag->id);
    $article->templates()->attach($template->id);
    $article->related()->attach($relatedArticle->id);

    ArticleProperty::factory()->for($article)->create([
        'name' => 'System',
        'content' => 'Stanton',
    ]);

    $response = $this->get(route('web.galactapedia.show', $article->cig_id));

    $response->assertOk()
        ->assertViewIs('galactapedia.show')
        ->assertSee('ArcCorp')
        ->assertSee('ArcCorp is a vast city planet.')
        ->assertSee('Locations')
        ->assertSee('Planet')
        ->assertSee('System')
        ->assertSee('Stanton')
        ->assertSee('Related Articles');
});
