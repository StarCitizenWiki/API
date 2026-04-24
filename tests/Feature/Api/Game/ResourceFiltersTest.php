<?php

declare(strict_types=1);

use App\Models\Game\Commodity\Commodity;
use App\Models\Game\GameVersion;
use App\Models\Game\Resource\Resource;
use App\Models\Game\Resource\ResourceCommodity;
use App\Models\Game\Resource\ResourceData;
use App\Models\Game\Resource\ResourceLocation;
use App\Models\Game\StarmapLocation;
use App\Models\Game\StarmapLocationData;
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
    Commodity::factory()->create(['name' => 'Beryl', 'tier' => 'common']);

    $response = $this->getJson('/api/commodities/filters');

    $response->assertSuccessful()
        ->assertJsonStructure([
            'filters' => [
                'rarity',
                'refined_version',
                'system',
                'type',
                'kind',
                'location',
            ],
        ]);
});

it('returns rarity facet from commodity data', function (): void {
    Commodity::factory()->create(['name' => 'Beryl', 'tier' => 'common']);
    Commodity::factory()->create(['name' => 'Quantainium', 'tier' => 'rare']);
    Commodity::factory()->create(['name' => 'TradeGood', 'tier' => null]);

    $response = $this->getJson('/api/commodities/filters');

    $response->assertSuccessful();

    $rarities = collect($response->json('filters.rarity'));
    expect($rarities->where('value', 'common')->count())->toBe(1);
    expect($rarities->where('value', 'rare')->count())->toBe(1);
    expect($rarities->where('value', null)->count())->toBe(1);

    $common = $rarities->firstWhere('value', 'common');
    expect($common['label'])->toBe('Common');
    $rare = $rarities->firstWhere('value', 'rare');
    expect($rare['label'])->toBe('Rare');
});

it('returns system facet from linked starmap locations', function (): void {
    $commodity = Commodity::factory()->create(['name' => 'Agricium']);
    linkCommodityToLocation($commodity, 'Stanton', 'Planet', 'Daymar');

    Commodity::factory()->create(['name' => 'TradeGood']);

    $response = $this->getJson('/api/commodities/filters');

    $response->assertSuccessful();

    $systems = collect($response->json('filters.system'));
    $stanton = $systems->firstWhere('value', 'Stanton');
    expect($stanton)->not->toBeNull()
        ->and($stanton['count'])->toBe(1);

    $nullSystem = $systems->firstWhere('value', null);
    expect($nullSystem)->not->toBeNull()
        ->and($nullSystem['count'])->toBe(1);
});

it('returns type facet from linked starmap locations', function (): void {
    $commodity = Commodity::factory()->create(['name' => 'Agricium']);
    linkCommodityToLocation($commodity, 'Stanton', 'Planet', 'Daymar');

    $response = $this->getJson('/api/commodities/filters');

    $types = collect($response->json('filters.type'));
    $planet = $types->firstWhere('value', 'Planet');
    expect($planet)->not->toBeNull()
        ->and($planet['count'])->toBe(1);
});

it('returns kind facet from resource data', function (): void {
    $mineableCommodity = Commodity::factory()->create(['name' => 'Agricium']);
    linkCommodityToResourceData($mineableCommodity, 'mineable');

    $response = $this->getJson('/api/commodities/filters');

    $kinds = collect($response->json('filters.kind'));
    $mineable = $kinds->firstWhere('value', 'mineable');
    expect($mineable)->not->toBeNull()
        ->and($mineable['count'])->toBe(1)
        ->and($mineable['label'])->toBe('Mineable');
});

it('returns refined_version facet', function (): void {
    Commodity::factory()->create([
        'name' => 'Raw Beryl',
        'refined_version_name' => 'Processed Beryl',
    ]);
    Commodity::factory()->create([
        'name' => 'TradeGood',
        'refined_version_name' => null,
    ]);

    $response = $this->getJson('/api/commodities/filters');

    $refined = collect($response->json('filters.refined_version'));
    $processed = $refined->firstWhere('value', 'Processed Beryl');
    expect($processed)->not->toBeNull()
        ->and($processed['count'])->toBe(1);
});

it('excludes resource data from other game versions', function (): void {
    $otherVersion = GameVersion::factory()->create([
        'code' => '4.0.0-PTU',
        'channel' => 'ptu',
        'released_at' => now()->subDay(),
        'is_default' => false,
    ]);

    $commodity = Commodity::factory()->create(['name' => 'Agricium']);
    $resource = Resource::factory()->create();
    $resourceData = ResourceData::factory()->create([
        'resource_id' => $resource->id,
        'game_version_id' => $otherVersion->id,
        'kind' => 'mineable',
    ]);
    ResourceCommodity::create([
        'resource_data_id' => $resourceData->id,
        'commodity_id' => $commodity->id,
    ]);

    $response = $this->getJson('/api/commodities/filters');

    $kinds = collect($response->json('filters.kind'));
    expect($kinds->firstWhere('value', 'mineable'))->toBeNull();
});

it('returns location facet from linked starmap locations', function (): void {
    $commodity = Commodity::factory()->create(['name' => 'Agricium']);
    linkCommodityToLocation($commodity, 'Stanton', 'Planet', 'Daymar');

    $response = $this->getJson('/api/commodities/filters');

    $response->assertSuccessful();

    $locations = collect($response->json('filters.location'));
    $daymar = $locations->firstWhere('value', 'Daymar');
    expect($daymar)->not->toBeNull()
        ->and($daymar['count'])->toBe(1);
});

it('filters by mineable=true returning only commodities with resource data', function (): void {
    $mineable = Commodity::factory()->create(['name' => 'Agricium', 'key' => 'Agricium']);
    linkCommodityToResourceData($mineable, 'mineable');

    Commodity::factory()->create(['name' => 'TradeGood', 'key' => 'TradeGood']);

    $response = $this->getJson('/api/commodities?filter[mineable]=true');

    $response->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.key', 'Agricium');
});

it('filters by mineable=false returning only trade-only commodities', function (): void {
    $mineable = Commodity::factory()->create(['name' => 'Agricium', 'key' => 'Agricium']);
    linkCommodityToResourceData($mineable, 'mineable');

    Commodity::factory()->create(['name' => 'TradeGood', 'key' => 'TradeGood']);

    $response = $this->getJson('/api/commodities?filter[mineable]=false');

    $response->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.key', 'TradeGood');
});

function linkCommodityToLocation(
    Commodity $commodity,
    string $system,
    string $typeName,
    string $locationName,
    string $groupName = 'SpaceShip_Mineables',
): void {
    $test = test();
    $resourceData = linkCommodityToResourceData($commodity, 'mineable');

    $starmapLocation = StarmapLocation::factory()->create();
    $locationData = StarmapLocationData::factory()->create([
        'starmap_location_id' => $starmapLocation->id,
        'game_version_id' => $test->defaultVersion->id,
        'name' => $locationName,
        'type_name' => $typeName,
        'system' => $system,
    ]);

    $resourceLocation = ResourceLocation::factory()->create([
        'resource_data_id' => $resourceData->id,
        'resource_kind' => 'mineable',
        'group_name' => $groupName,
    ]);

    $resourceLocation->starmapLocationData()->attach($locationData->id);
}

function linkCommodityToResourceData(Commodity $commodity, string $kind = 'mineable'): ResourceData
{
    $test = test();
    $resource = Resource::factory()->create();
    $resourceData = ResourceData::factory()->create([
        'resource_id' => $resource->id,
        'game_version_id' => $test->defaultVersion->id,
        'kind' => $kind,
    ]);

    ResourceCommodity::create([
        'resource_data_id' => $resourceData->id,
        'commodity_id' => $commodity->id,
    ]);

    return $resourceData;
}
