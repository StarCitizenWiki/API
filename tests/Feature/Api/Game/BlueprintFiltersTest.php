<?php

declare(strict_types=1);

use App\Models\Game\Blueprint;
use App\Models\Game\BlueprintData;
use App\Models\Game\Commodity\Commodity;
use App\Models\Game\GameVersion;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->defaultVersion = GameVersion::factory()->create([
        'code' => '4.0.0-LIVE',
        'channel' => 'live',
        'released_at' => now(),
        'is_default' => true,
    ]);
});

it('returns all expected facet keys', function (): void {
    $response = $this->getJson('/api/blueprints/filters');

    $response->assertSuccessful()
        ->assertJsonStructure([
            'filters' => [
                'ingredient.uuid',
                'resource.uuid',
            ],
        ]);
});

it('returns ingredient facets with counts', function (): void {
    $iron = Commodity::factory()->create(['name' => 'Iron']);
    $titanium = Commodity::factory()->create(['name' => 'Titanium']);

    BlueprintData::factory()
        ->for(Blueprint::factory(), 'blueprint')
        ->for($this->defaultVersion, 'gameVersion')
        ->withIngredients($iron, $titanium)
        ->create(['data' => ['tiers' => []]]);

    BlueprintData::factory()
        ->for(Blueprint::factory(), 'blueprint')
        ->for($this->defaultVersion, 'gameVersion')
        ->withIngredients($iron)
        ->create(['data' => ['tiers' => []]]);

    $response = $this->getJson('/api/blueprints/filters');

    $response->assertSuccessful();

    $ingredients = $response->json('filters')['ingredient.uuid'] ?? [];
    expect($ingredients)->toHaveCount(2)
        ->and($ingredients[0]['label'])->toBe('Iron')
        ->and($ingredients[0]['count'])->toBe(2)
        ->and($ingredients[1]['label'])->toBe('Titanium')
        ->and($ingredients[1]['count'])->toBe(1);
});

it('returns combined resource.uuid facets with union counts', function (): void {
    $hephaestanite = Commodity::factory()->create(['name' => 'Hephaestanite']);
    $iron = Commodity::factory()->create(['name' => 'Iron']);

    BlueprintData::factory()
        ->for(Blueprint::factory(), 'blueprint')
        ->for($this->defaultVersion, 'gameVersion')
        ->withIngredients($hephaestanite)
        ->withDismantleReturns([
            ['commodity' => $hephaestanite, 'quantity_scu' => 0.02],
        ])
        ->create(['data' => ['tiers' => []]]);

    BlueprintData::factory()
        ->for(Blueprint::factory(), 'blueprint')
        ->for($this->defaultVersion, 'gameVersion')
        ->withIngredients($iron)
        ->create(['data' => ['tiers' => []]]);

    $response = $this->getJson('/api/blueprints/filters');

    $response->assertSuccessful();

    $resourceFacets = $response->json('filters')['resource.uuid'] ?? [];
    expect($resourceFacets)->toHaveCount(2);

    $hephFacet = collect($resourceFacets)->first(fn (array $f): bool => $f['value'] === $hephaestanite->uuid);
    expect($hephFacet)->not->toBeNull()
        ->and($hephFacet['label'])->toBe('Hephaestanite')
        ->and($hephFacet['count'])->toBe(2);

    $ironFacet = collect($resourceFacets)->first(fn (array $f): bool => $f['value'] === $iron->uuid);
    expect($ironFacet)->not->toBeNull()
        ->and($ironFacet['label'])->toBe('Iron')
        ->and($ironFacet['count'])->toBe(1);
});
