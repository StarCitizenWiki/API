<?php

declare(strict_types=1);

use App\Models\Game\Blueprint;
use App\Models\Game\BlueprintData;
use App\Models\Game\Commodity\Commodity;
use App\Models\Game\GameVersion;
use App\Models\Game\Item;
use App\Models\Game\ItemData;

beforeEach(function (): void {
    $this->defaultVersion = GameVersion::factory()->create([
        'code' => '4.0.0-LIVE',
        'channel' => 'live',
        'released_at' => now(),
        'is_default' => true,
    ]);

    $this->requestedVersion = GameVersion::factory()->create([
        'code' => '4.0.0-PTU',
        'channel' => 'ptu',
        'released_at' => now()->subDay(),
        'is_default' => false,
    ]);
});

it('lists commodities with versioned links', function (): void {
    $alpha = Commodity::factory()->create([
        'key' => 'AlphaResource',
        'name' => 'Alpha Resource',
    ]);

    Commodity::factory()->create([
        'key' => 'BetaResource',
        'name' => 'Beta Resource',
    ]);

    $this->getJson('/api/commodities')
        ->assertSuccessful()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.uuid', $alpha->uuid)
        ->assertJsonPath('data.0.link', route('commodities.show', ['commodity' => $alpha->uuid]));
});

it('exposes volatility fields in the commodity show response', function (): void {
    $commodity = Commodity::factory()->create([
        'key' => 'VolatileOre',
        'name' => 'Volatile Ore',
        'volatility' => 7.5,
        'volatility_health_decay_per_second' => 1.25,
    ]);

    $this->getJson(route('commodities.show', ['commodity' => $commodity->uuid]))
        ->assertSuccessful()
        ->assertJsonPath('data.volatility', 7.5)
        ->assertJsonPath('data.volatility_health_decay_per_second', 1.25);
});

it('can filter commodities to only those used by blueprints for the resolved game version', function (): void {
    $usedInDefault = Commodity::factory()->create([
        'key' => 'UsedDefault',
        'name' => 'Used Default',
    ]);

    $usedInRequested = Commodity::factory()->create([
        'key' => 'UsedRequested',
        'name' => 'Used Requested',
    ]);

    Commodity::factory()->create([
        'key' => 'UnusedResource',
        'name' => 'Unused Resource',
    ]);

    BlueprintData::factory()
        ->for(Blueprint::factory(), 'blueprint')
        ->for($this->defaultVersion, 'gameVersion')
        ->withIngredients($usedInDefault)
        ->create([
            'data' => [
                'tiers' => [],
            ],
        ]);

    BlueprintData::factory()
        ->for(Blueprint::factory(), 'blueprint')
        ->for($this->requestedVersion, 'gameVersion')
        ->withIngredients($usedInRequested)
        ->create([
            'data' => [
                'tiers' => [],
            ],
        ]);

    $defaultResponse = $this->getJson('/api/commodities?'.http_build_query([
        'filter' => [
            'used' => true,
        ],
    ]));

    $requestedResponse = $this->getJson('/api/commodities?'.http_build_query([
        'version' => $this->requestedVersion->code,
        'filter' => [
            'used' => true,
        ],
    ]));

    $defaultResponse->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.uuid', $usedInDefault->uuid);

    $requestedResponse->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.uuid', $usedInRequested->uuid)
        ->assertJsonPath(
            'data.0.link',
            route('commodities.show', [
                'commodity' => $usedInRequested->uuid,
                'version' => $this->requestedVersion->code,
            ]),
        );
});

it('rejects invalid used filters', function (): void {
    $response = $this->getJson('/api/commodities?'.http_build_query([
        'filter' => [
            'used' => 'maybe',
        ],
    ]));

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['filter.used']);
});

it('returns blueprints that consume a commodity for the resolved game version', function (): void {
    $resourceType = Commodity::factory()->create();
    $otherResourceType = Commodity::factory()->create();

    $matchingBlueprint = Blueprint::factory()->create();
    BlueprintData::factory()
        ->for($matchingBlueprint, 'blueprint')
        ->for($this->defaultVersion, 'gameVersion')
        ->withIngredients($resourceType, $otherResourceType)
        ->create([
            'key' => 'BP_MATCHING',
            'output_item_uuid' => fake()->uuid(),
            'data' => [
                'tiers' => [],
            ],
        ]);

    $nonMatchingBlueprint = Blueprint::factory()->create();
    BlueprintData::factory()
        ->for($nonMatchingBlueprint, 'blueprint')
        ->for($this->defaultVersion, 'gameVersion')
        ->withIngredients($otherResourceType)
        ->create([
            'key' => 'BP_NON_MATCHING',
            'output_item_uuid' => fake()->uuid(),
            'data' => [
                'tiers' => [],
            ],
        ]);

    BlueprintData::factory()
        ->for($matchingBlueprint, 'blueprint')
        ->for($this->requestedVersion, 'gameVersion')
        ->withIngredients($resourceType)
        ->create([
            'key' => 'BP_MATCHING_PTU',
            'output_item_uuid' => fake()->uuid(),
            'data' => [
                'tiers' => [],
            ],
        ]);

    $response = $this->getJson(route('commodities.show', ['commodity' => $resourceType->uuid, 'include' => 'blueprints']));

    $response->assertSuccessful()
        ->assertJsonPath('data.uuid', $resourceType->uuid)
        ->assertJsonCount(1, 'data.blueprints')
        ->assertJsonPath('data.blueprints.0.key', 'BP_MATCHING')
        ->assertJsonPath('data.blueprints.0.output_item_uuid', BlueprintData::where('blueprint_id', $matchingBlueprint->id)->where('game_version_id', $this->defaultVersion->id)->first()->output_item_uuid);
});

it('returns items that have a commodity in their default composition', function (): void {
    $resourceType = Commodity::factory()->create([
        'key' => 'TestResource',
        'name' => 'Test Resource',
    ]);

    $itemWithResource = Item::factory()->create();
    $itemData = ItemData::factory()
        ->for($itemWithResource, 'item')
        ->for($this->defaultVersion, 'gameVersion')
        ->hasAttached($resourceType, [], 'commodities')
        ->create([
            'name' => 'Item With Resource',
        ]);

    $itemWithoutResource = Item::factory()->create();
    ItemData::factory()
        ->for($itemWithoutResource, 'item')
        ->for($this->defaultVersion, 'gameVersion')
        ->create([
            'name' => 'Item Without Resource',
        ]);

    $response = $this->getJson(route('commodities.show', ['commodity' => $resourceType->uuid, 'include' => 'items']));

    $response->assertSuccessful()
        ->assertJsonPath('data.uuid', $resourceType->uuid)
        ->assertJsonCount(1, 'data.items')
        ->assertJsonPath('data.items.0.name', 'Item With Resource')
        ->assertJsonPath('data.items.0.uuid', $itemWithResource->uuid)
        ->assertJsonPath('data.items.0.type', $itemData->type)
        ->assertJsonPath('data.items.0.sub_type', $itemData->sub_type)
        ->assertJsonPath('data.items.0.size', $itemData->size);
});
