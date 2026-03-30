<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\StarCitizen\Galactapedia\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('searches galactapedia by text query without bigint cast errors', function (): void {
    GameVersion::factory()->create([
        'code' => '3.25.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $matchingArticle = Article::factory()->create([
        'cig_id' => 91001,
        'title' => 'Flight model guide',
        'slug' => 'flight-model-guide',
    ]);

    Article::factory()->create([
        'cig_id' => 91002,
        'title' => 'Armor update',
        'slug' => 'armor-update',
    ]);

    $response = $this->postJson(route('galactapedia.search'), [
        'query' => 'flight model',
    ]);

    $response->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertHeader('Deprecated', 'true');

    $ids = collect($response->json('data'))
        ->pluck('id')
        ->map(static fn (mixed $id): int => (int) $id);

    expect($response->json('meta.deprecated'))->toBeTrue()
        ->and($ids->all())->toBe([$matchingArticle->cig_id]);
});

it('searches galactapedia by numeric cig id', function (): void {
    GameVersion::factory()->create([
        'code' => '3.25.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $targetArticle = Article::factory()->create([
        'cig_id' => 91011,
        'title' => 'Roadmap roundup',
        'slug' => 'roadmap-roundup',
    ]);

    Article::factory()->create([
        'cig_id' => 91012,
        'title' => 'Ship spotlight',
        'slug' => 'ship-spotlight',
    ]);

    $response = $this->postJson(route('galactapedia.search'), [
        'query' => (string) $targetArticle->cig_id,
    ]);

    $response->assertSuccessful()
        ->assertJsonCount(1, 'data');

    $ids = collect($response->json('data'))
        ->pluck('id')
        ->map(static fn (mixed $id): int => (int) $id);

    expect($ids->all())
        ->toBe([$targetArticle->cig_id]);
});
