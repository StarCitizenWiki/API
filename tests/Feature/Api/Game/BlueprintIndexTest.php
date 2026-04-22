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

it('includes ingredient names backfilled from the relationship on index', function (): void {
    $iron = Commodity::factory()->create(['name' => 'Iron']);

    BlueprintData::factory()
        ->for(Blueprint::factory(), 'blueprint')
        ->for($this->defaultVersion, 'gameVersion')
        ->withIngredients($iron)
        ->create([
            'key' => 'BP_IRON_TEST',
            'output_name' => 'Iron Widget',
            'data' => ['tiers' => []],
        ]);

    $response = $this->getJson('/api/blueprints');

    $response->assertSuccessful()
        ->assertJsonPath('data.0.ingredients.0.name', 'Iron')
        ->assertJsonPath('data.0.ingredients.0.resource_type_uuid', $iron->uuid);
});

it('includes ingredient quantity_scu from tier data on index', function (): void {
    $lindinium = Commodity::factory()->create([
        'uuid' => $lindiniumUuid = fake()->uuid(),
        'name' => 'Lindinium',
    ]);

    BlueprintData::factory()
        ->for(Blueprint::factory(), 'blueprint')
        ->for($this->defaultVersion, 'gameVersion')
        ->withIngredients($lindinium)
        ->create([
            'output_name' => 'Tiered Item',
            'data' => [
                'tiers' => [
                    [
                        'requirements' => [
                            'kind' => 'root',
                            'children' => [
                                ['kind' => 'resource', 'uuid' => $lindiniumUuid, 'name' => 'Lindinium', 'quantity_scu' => 0.06],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

    $response = $this->getJson('/api/blueprints');

    $response->assertSuccessful()
        ->assertJsonPath('data.0.ingredients.0.quantity_scu', 0.06);
});

it('includes dismantle returns with quantity_scu on index', function (): void {
    $hephaestanite = Commodity::factory()->create(['name' => 'Hephaestanite']);

    BlueprintData::factory()
        ->for(Blueprint::factory(), 'blueprint')
        ->for($this->defaultVersion, 'gameVersion')
        ->withDismantleReturns([
            ['commodity' => $hephaestanite, 'quantity_scu' => 0.02],
        ])
        ->create([
            'output_name' => 'Dismantle Widget',
            'data' => ['tiers' => []],
        ]);

    $response = $this->getJson('/api/blueprints');

    $response->assertSuccessful()
        ->assertJsonPath('data.0.dismantle_returns.0.name', 'Hephaestanite')
        ->assertJsonPath('data.0.dismantle_returns.0.resource_type_uuid', $hephaestanite->uuid)
        ->assertJsonPath('data.0.dismantle_returns.0.quantity_scu', 0.02);
});

it('does not include detail-only fields on index', function (): void {
    BlueprintData::factory()
        ->for(Blueprint::factory(), 'blueprint')
        ->for($this->defaultVersion, 'gameVersion')
        ->create(['output_name' => 'Simple Widget']);

    $response = $this->getJson('/api/blueprints');

    $response->assertSuccessful()
        ->assertJsonMissing(['dismantle'])
        ->assertJsonMissing(['requirement_groups'])
        ->assertJsonMissing(['tiers']);
});

it('includes ingredient and dismantle return links on index', function (): void {
    $iron = Commodity::factory()->create(['name' => 'Iron']);

    BlueprintData::factory()
        ->for(Blueprint::factory(), 'blueprint')
        ->for($this->defaultVersion, 'gameVersion')
        ->withIngredients($iron)
        ->withDismantleReturns([
            ['commodity' => $iron, 'quantity_scu' => 0.01],
        ])
        ->create([
            'output_name' => 'Linked Widget',
            'data' => ['tiers' => []],
        ]);

    $response = $this->getJson('/api/blueprints');

    $response->assertSuccessful()
        ->assertJsonPath('data.0.ingredients.0.link', route('commodities.show', ['commodity' => $iron->uuid]))
        ->assertJsonPath('data.0.ingredients.0.web_url', route('web.commodities.show', ['identifier' => $iron->uuid]))
        ->assertJsonPath('data.0.dismantle_returns.0.link', route('commodities.show', ['commodity' => $iron->uuid]))
        ->assertJsonPath('data.0.dismantle_returns.0.web_url', route('web.commodities.show', ['identifier' => $iron->uuid]));
});

it('includes item-kind ingredients with links and quantity on index', function (): void {
    $hadaniteUuid = fake()->uuid();
    $lindinium = Commodity::factory()->create([
        'uuid' => $lindiniumUuid = fake()->uuid(),
        'name' => 'Lindinium',
    ]);

    BlueprintData::factory()
        ->for(Blueprint::factory(), 'blueprint')
        ->for($this->defaultVersion, 'gameVersion')
        ->withIngredients($lindinium)
        ->create([
            'output_name' => 'Item Ingredient Widget',
            'data' => [
                'tiers' => [
                    [
                        'requirements' => [
                            'kind' => 'root',
                            'children' => [
                                ['kind' => 'resource', 'uuid' => $lindiniumUuid, 'name' => 'Lindinium', 'quantity_scu' => 0.06],
                                ['kind' => 'item', 'uuid' => $hadaniteUuid, 'name' => 'Hadanite', 'quantity' => 1],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

    $response = $this->getJson('/api/blueprints');

    $response->assertSuccessful();

    $ingredients = $response->json('data.0.ingredients');

    $resourceIngredient = collect($ingredients)->first(fn (array $i): bool => ($i['kind'] ?? null) === 'resource');
    $itemIngredient = collect($ingredients)->first(fn (array $i): bool => ($i['kind'] ?? null) === 'item');

    expect($resourceIngredient)
        ->toHaveKey('resource_type_uuid', $lindiniumUuid)
        ->toHaveKey('quantity_scu', 0.06)
        ->and($resourceIngredient['link'])->toBe(route('commodities.show', ['commodity' => $lindiniumUuid]))
        ->and($resourceIngredient['web_url'])->toBe(route('web.commodities.show', ['identifier' => $lindiniumUuid]));

    expect($itemIngredient)
        ->toHaveKey('kind', 'item')
        ->toHaveKey('item_uuid', $hadaniteUuid)
        ->toHaveKey('quantity', 1)
        ->and($itemIngredient['link'])->toBe(route('items.show', ['identifier' => $hadaniteUuid]))
        ->and($itemIngredient['web_url'])->toBe(route('web.items.show', ['item' => $hadaniteUuid]));
});
