<?php

declare(strict_types=1);

use App\Models\StarCitizen\Galactapedia\Article;
use App\Models\StarCitizen\Galactapedia\Category;
use App\Models\StarCitizen\Galactapedia\Tag;
use App\Models\StarCitizen\Galactapedia\Template;

it('returns galactapedia filter values with counts', function (): void {
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

    assertFacetsEqual(
        $this->getJson(route('galactapedia.filters'))->assertOk(),
        [
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
    );
});

describe('creation date filter', function (): void {
    beforeEach(function (): void {
        $category = Category::factory()->create(['name' => 'Lore']);
        $template = Template::factory()->create(['template' => 'Civilization']);

        $matching = Article::factory()->create(['created_at' => '2025-03-15 12:00:00']);
        $matching->categories()->attach($category);
        $matching->templates()->attach($template);

        $other = Article::factory()->create(['created_at' => '2024-03-15 12:00:00']);
        $other->categories()->attach($category);
        $other->templates()->attach($template);
    });

    it('narrows joined facets by article creation date', function (string $date): void {
        $response = $this->getJson(route('galactapedia.filters', [
            'filter' => ['created_at' => $date, 'template' => 'Civilization'],
        ]))->assertOk();

        assertFacetsEqual($response, [
            'category' => [['value' => 'Lore', 'label' => 'Lore', 'count' => 1]],
            'tag' => [['value' => null, 'label' => 'Unknown', 'count' => 1]],
            'template' => [['value' => 'Civilization', 'label' => 'Civilization', 'count' => 1]],
        ]);
    })->with(['2025', '2025-03', '2025-03-15']);
});

it('narrows facets when a filter is supplied', function (): void {
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

    assertFacetsEqual(
        $this->getJson(route('galactapedia.filters', [
            'filter' => ['category' => 'Lore'],
        ]))->assertOk(),
        [
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
    );
});
