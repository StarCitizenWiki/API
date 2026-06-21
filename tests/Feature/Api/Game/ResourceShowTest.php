<?php

declare(strict_types=1);

use App\Models\Game\Commodity\Commodity;
use App\Models\Game\Resource\Resource;
use App\Models\Game\Resource\ResourceCommodity;
use App\Models\Game\Resource\ResourceData;
use App\Models\Game\Resource\ResourceLocation;
use App\Models\Game\Resource\ResourceProvider;
use App\Models\Game\StarmapLocation;
use App\Models\Game\StarmapLocationData;

use function Tests\Support\attachLocation;
use function Tests\Support\createResourceData;

beforeEach(function (): void {
    $this->defaultVersion = createDefaultGameVersion();
});

it('returns 404 for unknown resource', function (): void {
    $response = $this->getJson('/api/commodities/non-existent-slug');

    $response->assertNotFound();
});

it('resolves resource by uuid', function (): void {
    $commodity = Commodity::factory()->create(['name' => 'Beryl']);

    $response = $this->getJson("/api/commodities/{$commodity->uuid}");

    $response->assertSuccessful()
        ->assertJsonPath('data.uuid', $commodity->uuid)
        ->assertJsonPath('data.name', 'Beryl');
});

it('resolves resource by slug', function (): void {
    $commodity = Commodity::factory()->create([
        'name' => 'Beryl',
        'slug' => 'beryl-ore',
    ]);

    $response = $this->getJson('/api/commodities/beryl-ore');

    $response->assertSuccessful()
        ->assertJsonPath('data.uuid', $commodity->uuid);
});

it('returns basic commodity fields', function (): void {
    $commodity = Commodity::factory()->create([
        'name' => 'Beryl',
        'tier' => 'common',
        'slug' => 'beryl',
        'description' => 'A useful mineral.',
        'density_g_per_cc' => 3.5,
        'instability' => 0.1,
        'resistance' => 0.5,
        'box_sizes_scu' => [1, 2, 4],
        'validate_default_cargo_box' => true,
        'has_default_cargo_containers' => false,
    ]);

    $response = $this->getJson("/api/commodities/{$commodity->uuid}");

    $response->assertSuccessful()
        ->assertJsonPath('data.key', $commodity->key)
        ->assertJsonPath('data.name', 'Beryl')
        ->assertJsonPath('data.slug', 'beryl')
        ->assertJsonPath('data.description', 'A useful mineral.')
        ->assertJsonPath('data.tier', 'common')
        ->assertJsonPath('data.box_sizes_scu', [1, 2, 4])
        ->assertJsonPath('data.validate_default_cargo_box', true)
        ->assertJsonPath('data.has_default_cargo_containers', false);
});

it('returns refined version info', function (): void {
    $refined = Commodity::factory()->create(['name' => 'Aluminum']);
    $raw = Commodity::factory()->create([
        'name' => 'Aluminum (Ore)',
        'refined_version_uuid' => $refined->uuid,
        'refined_version_name' => 'Aluminum',
    ]);

    $response = $this->getJson("/api/commodities/{$raw->uuid}");

    $response->assertSuccessful()
        ->assertJsonPath('data.refined_version.name', 'Aluminum')
        ->assertJsonPath('data.refined_version.uuid', $refined->uuid);
});

it('returns is_mineable false when no resource data exists', function (): void {
    $commodity = Commodity::factory()->create(['name' => 'TradeGood']);

    $response = $this->getJson("/api/commodities/{$commodity->uuid}");

    $response->assertSuccessful()
        ->assertJsonPath('data.is_mineable', false)
        ->assertJsonPath('data.has_ship_mineables', false)
        ->assertJsonPath('data.has_ground_vehicle_mineables', false)
        ->assertJsonPath('data.has_fps_mineables', false)
        ->assertJsonPath('data.has_harvestables', false)
        ->assertJsonPath('data.has_salvage', false);
});

it('returns is_mineable true and flags when resource data exists', function (): void {
    $commodity = Commodity::factory()->create(['name' => 'Beryl']);
    $resourceData = createResourceData($commodity, 'mineable');
    attachLocation($resourceData, 'Stanton', 'Planet', 'Daymar', 'SpaceShip_Mineables', 'mineable');

    $response = $this->getJson("/api/commodities/{$commodity->uuid}");

    $response->assertSuccessful()
        ->assertJsonPath('data.is_mineable', true)
        ->assertJsonPath('data.has_ship_mineables', true);
});

it('returns detailed location entries with quality data', function (): void {
    $commodity = Commodity::factory()->create(['name' => 'Gold']);
    $resourceData = createResourceData($commodity, 'mineable');
    $resourceLocation = attachLocation($resourceData, 'Stanton', 'Planet', 'microTech', 'SpaceShip_Mineables', 'mineable', [
        'group_probability' => 0.35,
        'relative_probability' => 0.5,
        'quality_min' => 245,
        'quality_max' => 490,
        'quality_mean' => 367,
        'quality_stddev' => 102,
    ]);
    $starmapLocation = $resourceLocation->starmapLocationData()->first()->location;

    $response = $this->getJson("/api/commodities/{$commodity->uuid}");

    $response->assertSuccessful();

    $locations = $response->json('data.locations');
    expect($locations)->toHaveCount(1);

    $loc = $locations[0];
    expect($loc['name'])->toBe('microTech')
        ->and($loc['system'])->toBe('Stanton')
        ->and($loc['type'])->toBe('Planet')
        ->and($loc['uuid'])->toBe($starmapLocation->uuid)
        ->and($loc['resources'])->toHaveCount(1);

    $deposit = $loc['resources'][0];
    expect($deposit['key'])->toBe($resourceData->key)
        ->and($deposit['group_name'])->toBe('SpaceShip_Mineables')
        ->and($deposit['resource_kind'])->toBe('mineable');

    $entry = $deposit['materials'][0];
    expect($entry['quality_min'])->toBe(245)
        ->and($entry['quality_max'])->toBe(490)
        ->and($entry['quality_mean'])->toBe(367)
        ->and($entry['quality_stddev'])->toBe(102)
        ->and($entry['group_probability'])->toBe(0.35)
        ->and($entry['relative_probability'])->toBe(0.5);
});

it('returns areas from resource provider', function (): void {
    $commodity = Commodity::factory()->create(['name' => 'Beryl']);
    $resourceData = createResourceData($commodity, 'mineable');

    $provider = ResourceProvider::factory()->create([
        'game_version_id' => $this->defaultVersion->id,
        'areas' => [
            ['name' => 'Desert', 'global_modifier' => 1, 'modifiers' => []],
            ['name' => 'Rivers', 'global_modifier' => 50, 'modifiers' => [
                ['modifier' => 1, 'resource_uuid' => $resourceData->resource->uuid, 'group_name' => 'SpaceShip_Mineables'],
            ]],
        ],
    ]);

    attachLocation($resourceData, 'Stanton', 'Planet', 'Hurston', 'SpaceShip_Mineables', 'mineable', [
        'resource_provider_id' => $provider->id,
    ]);

    $response = $this->getJson("/api/commodities/{$commodity->uuid}");

    $locationAreas = $response->json('data.locations.0.areas');
    expect($locationAreas)->toHaveCount(1)
        ->and($locationAreas[0])->toBe(['name' => 'Rivers', 'global_modifier' => 50]);

    $areaExceptions = $response->json('data.locations.0.resources.0.area_exceptions');
    expect($areaExceptions)->toBeNull();
});

it('returns clustering data from resource location', function (): void {
    $commodity = Commodity::factory()->create(['name' => 'Beryl']);
    $resourceData = createResourceData($commodity, 'mineable');
    attachLocation($resourceData, 'Stanton', 'Planet', 'microTech', 'SpaceShip_Mineables', 'mineable', [
        'data' => [
            'clustering' => [
                'Key' => 'CommonShipMineable_Cluster',
                'MinSize' => 4,
                'MaxSize' => 6,
                'MinProximity' => 1,
                'MaxProximity' => 12,
                'ProbabilityOfClustering' => 1,
                'Params' => [
                    [
                        'MinSize' => 5,
                        'MaxSize' => 5,
                        'MinProximity' => 8,
                        'MaxProximity' => 1,
                        'RelativeProbability' => 0.3,
                    ],
                ],
            ],
        ],
    ]);

    $response = $this->getJson("/api/commodities/{$commodity->uuid}");

    $clustering = $response->json('data.locations.0.resources.0.clustering');
    expect($clustering['key'])->toBe('CommonShipMineable_Cluster')
        ->and($clustering['min_size'])->toBe(4)
        ->and($clustering['max_size'])->toBe(6)
        ->and($clustering['probability'])->toBe(1)
        ->and($clustering['probability_percent'])->toBe(100);
});

it('separates deposits by provider at same location', function (): void {
    $commodity = Commodity::factory()->create(['name' => 'Copper']);
    $resourceData = createResourceData($commodity, 'mineable');

    $starmapLocation = StarmapLocation::factory()->create();
    $locationData = StarmapLocationData::factory()->create([
        'starmap_location_id' => $starmapLocation->id,
        'game_version_id' => $this->defaultVersion->id,
        'name' => 'Hurston',
        'type_name' => 'Planet',
        'system' => 'Stanton',
    ]);

    $caveProvider = ResourceProvider::factory()->create([
        'game_version_id' => $this->defaultVersion->id,
        'areas' => [
            ['name' => 'Deep Tunnel', 'global_modifier' => 1, 'modifiers' => [
                ['modifier' => 5, 'resource_uuid' => $resourceData->resource->uuid, 'group_name' => 'SpaceShip_Mineables'],
            ]],
        ],
    ]);

    $surfaceProvider = ResourceProvider::factory()->create([
        'game_version_id' => $this->defaultVersion->id,
        'areas' => [],
    ]);

    $caveLocation = ResourceLocation::factory()->create([
        'resource_data_id' => $resourceData->id,
        'resource_provider_id' => $caveProvider->id,
        'group_name' => 'SpaceShip_Mineables',
        'resource_kind' => 'mineable',
        'group_probability' => 0.2,
        'relative_probability' => 0.3,
        'quality_min' => 100,
        'quality_max' => 500,
    ]);
    $caveLocation->starmapLocationData()->attach($locationData->id);

    $surfaceLocation = ResourceLocation::factory()->create([
        'resource_data_id' => $resourceData->id,
        'resource_provider_id' => $surfaceProvider->id,
        'group_name' => 'SpaceShip_Mineables',
        'resource_kind' => 'mineable',
        'group_probability' => 0.4,
        'relative_probability' => 0.6,
        'quality_min' => 50,
        'quality_max' => 300,
    ]);
    $surfaceLocation->starmapLocationData()->attach($locationData->id);

    $response = $this->getJson("/api/commodities/{$commodity->uuid}");

    $response->assertSuccessful();

    $resources = $response->json('data.locations.0.resources');
    expect($resources)->toHaveCount(2);

    $caveDeposit = collect($resources)->first(fn (array $r): bool => $r['area_exceptions'] !== null);
    $surfaceDeposit = collect($resources)->first(fn (array $r): bool => $r['area_exceptions'] === null);

    expect($caveDeposit)->not->toBeNull()
        ->and($caveDeposit['area_exceptions'])->toHaveCount(1)
        ->and($caveDeposit['area_exceptions'][0]['name'])->toBe('Deep Tunnel')
        ->and($caveDeposit['area_exceptions'][0]['modifier'])->toBe(5)
        ->and($caveDeposit['materials'][0]['group_probability'])->toBe(0.2);

    expect($surfaceDeposit)->not->toBeNull()
        ->and($surfaceDeposit['materials'][0]['group_probability'])->toBe(0.4)
        ->and($surfaceDeposit['materials'][0]['quality_min'])->toBe(50);
});

it('returns materials from resource location rows', function (): void {
    $gold = Commodity::factory()->create(['name' => 'Gold', 'key' => 'Ore_Gold']);
    $aluminum = Commodity::factory()->create(['name' => 'Aluminum (Ore)', 'key' => 'Ore_Aluminum']);

    $resource = Resource::factory()->create();
    $resourceData = ResourceData::factory()->create([
        'resource_id' => $resource->id,
        'game_version_id' => $this->defaultVersion->id,
        'key' => 'Granite_Deposit',
        'name' => 'Granite Deposit',
        'kind' => 'mineable',
    ]);

    ResourceCommodity::create([
        'resource_data_id' => $resourceData->id,
        'commodity_id' => $gold->id,
        'weight' => 1.0,
    ]);

    ResourceCommodity::create([
        'resource_data_id' => $resourceData->id,
        'commodity_id' => $aluminum->id,
        'probability' => 0.7,
        'min_percentage' => 30.0,
        'max_percentage' => 60.0,
        'quality_scale' => 1.0,
        'curve_exponent' => 1.0,
    ]);

    $starmapLocation = StarmapLocation::factory()->create();
    $locationData = StarmapLocationData::factory()->create([
        'starmap_location_id' => $starmapLocation->id,
        'game_version_id' => $this->defaultVersion->id,
        'name' => 'Daymar',
        'type_name' => 'Moon',
        'system' => 'Stanton',
    ]);

    $provider = ResourceProvider::factory()->create();

    $rlGold = ResourceLocation::factory()->create([
        'resource_data_id' => $resourceData->id,
        'resource_provider_id' => $provider->id,
        'group_name' => 'SpaceShip_Mineables',
        'resource_kind' => 'mineable',
        'commodity_id' => $gold->id,
    ]);
    $rlGold->starmapLocationData()->attach($locationData->id);

    $rlAluminum = ResourceLocation::factory()->create([
        'resource_data_id' => $resourceData->id,
        'resource_provider_id' => $provider->id,
        'group_name' => 'SpaceShip_Mineables',
        'resource_kind' => 'mineable',
        'commodity_id' => $aluminum->id,
    ]);
    $rlAluminum->starmapLocationData()->attach($locationData->id);

    $response = $this->getJson("/api/commodities/{$gold->uuid}");

    $materials = $response->json('data.locations.0.resources.0.materials');
    expect($materials)->toHaveCount(2);
    $aluminumEntry = collect($materials)->first(fn (array $c): bool => $c['key'] === 'Ore_Aluminum');
    expect($aluminumEntry['name'])->toBe('Aluminum (Ore)')
        ->and($aluminumEntry['is_current'])->toBeFalse();
    $goldEntry = collect($materials)->first(fn (array $c): bool => $c['key'] === 'Ore_Gold');
    expect($goldEntry['is_current'])->toBeTrue();
});

it('marks current commodity in materials', function (): void {
    $gold = Commodity::factory()->create(['name' => 'Gold', 'key' => 'Ore_Gold']);

    $resourceData = createResourceData($gold, 'mineable');
    attachLocation($resourceData, 'Stanton', 'Moon', 'Daymar', 'SpaceShip_Mineables', 'mineable', [
        'commodity_id' => $gold->id,
    ]);

    $response = $this->getJson("/api/commodities/{$gold->uuid}");

    $materials = $response->json('data.locations.0.resources.0.materials');
    expect($materials)->toHaveCount(1);
    expect($materials[0]['is_current'])->toBeTrue();
});

it('returns kind and systems arrays', function (): void {
    $commodity = Commodity::factory()->create(['name' => 'Beryl']);
    $resourceData = createResourceData($commodity, 'mineable');
    attachLocation($resourceData, 'Stanton', 'Planet', 'Daymar', 'SpaceShip_Mineables', 'mineable');

    $response = $this->getJson("/api/commodities/{$commodity->uuid}");

    $response->assertSuccessful();
    expect($response->json('data.kind'))->toBe('mineable')
        ->and($response->json('data.systems'))->toContain('Stanton');
});

it('returns null clustering when no data', function (): void {
    $commodity = Commodity::factory()->create(['name' => 'Beryl']);
    $resourceData = createResourceData($commodity, 'mineable');
    attachLocation($resourceData, 'Stanton', 'Moon', 'Daymar', 'SpaceShip_Mineables', 'mineable', ['data' => null]);

    $response = $this->getJson("/api/commodities/{$commodity->uuid}");

    $response->assertSuccessful();
    expect($response->json('data.locations.0.resources.0.clustering'))->toBeNull();
});

it('returns null areas when no provider areas', function (): void {
    $commodity = Commodity::factory()->create(['name' => 'Beryl']);
    $resourceData = createResourceData($commodity, 'mineable');
    attachLocation($resourceData, 'Stanton', 'Moon', 'Daymar');

    $response = $this->getJson("/api/commodities/{$commodity->uuid}");

    $response->assertSuccessful();
    expect($response->json('data.locations.0.resources.0.area_exceptions'))->toBeNull();
});

it('returns null quality_quantization when raw data has no quantization', function (): void {
    $commodity = Commodity::factory()->create(['name' => 'Beryl']);
    $resourceData = createResourceData($commodity, 'mineable');
    attachLocation($resourceData, 'Stanton', 'Planet', 'microTech', 'SpaceShip_Mineables', 'mineable');

    $response = $this->getJson("/api/commodities/{$commodity->uuid}");

    $response->assertSuccessful();
    $materials = $response->json('data.locations.0.resources.0.materials');
    expect($materials)->toHaveCount(1)
        ->and($materials[0]['quality_quantization'])->toBeNull()
        ->and($materials[0]['quality_quantized_values'])->toBeNull();
});

it('returns quality quantization values matched by commodity UUID and percentage range', function (): void {
    $goldQuantization = [318, 511, 614, 783, 896, 919, 953, 1000];
    $aluminumQuantization = [300, 500, 650, 750, 850, 925, 970, 1000];

    $gold = Commodity::factory()->create(['name' => 'Gold', 'key' => 'Ore_Gold']);
    $aluminum = Commodity::factory()->create(['name' => 'Aluminum (Ore)', 'key' => 'Ore_Aluminum']);

    $resourceData = createResourceData($gold, 'mineable', [
        'key' => 'GPI_Icicle',
        'name' => 'GPI Icicle',
        'data' => [
            'Composition' => [
                'Parts' => [
                    [
                        'ResourceTypeUUID' => $gold->uuid,
                        'Key' => 'Ore_Gold',
                        'Name' => 'Gold (Ore)',
                        'MinPercentage' => 10,
                        'MaxPercentage' => 30,
                        'Probability' => 1,
                        'QualityScale' => 1,
                        'CurveExponent' => 1,
                        'QualityQuantization' => $goldQuantization,
                    ],
                    [
                        'ResourceTypeUUID' => $aluminum->uuid,
                        'Key' => 'Ore_Aluminum',
                        'Name' => 'Aluminum (Ore)',
                        'MinPercentage' => 30,
                        'MaxPercentage' => 70,
                        'Probability' => 1,
                        'QualityScale' => 1,
                        'CurveExponent' => 1,
                        'QualityQuantization' => $aluminumQuantization,
                    ],
                ],
            ],
        ],
    ]);
    ResourceCommodity::create(['resource_data_id' => $resourceData->id, 'commodity_id' => $aluminum->id]);

    $starmapLocation = StarmapLocation::factory()->create();
    $locationData = StarmapLocationData::factory()->create([
        'starmap_location_id' => $starmapLocation->id,
        'game_version_id' => $this->defaultVersion->id,
        'name' => 'microTech',
        'type_name' => 'Planet',
        'system' => 'Stanton',
    ]);

    $provider = ResourceProvider::factory()->create();

    $rlGold = ResourceLocation::factory()->create([
        'resource_data_id' => $resourceData->id,
        'resource_provider_id' => $provider->id,
        'group_name' => 'SpaceShip_Mineables',
        'resource_kind' => 'mineable',
        'commodity_id' => $gold->id,
        'min_percentage' => 10,
        'max_percentage' => 30,
        'data' => ['quality_quantization' => $goldQuantization],
    ]);
    $rlGold->starmapLocationData()->attach($locationData->id);

    $rlAluminum = ResourceLocation::factory()->create([
        'resource_data_id' => $resourceData->id,
        'resource_provider_id' => $provider->id,
        'group_name' => 'SpaceShip_Mineables',
        'resource_kind' => 'mineable',
        'commodity_id' => $aluminum->id,
        'min_percentage' => 30,
        'max_percentage' => 70,
        'data' => ['quality_quantization' => $aluminumQuantization],
    ]);
    $rlAluminum->starmapLocationData()->attach($locationData->id);

    $response = $this->getJson("/api/commodities/{$gold->uuid}");

    $response->assertSuccessful();
    $materials = $response->json('data.locations.0.resources.0.materials');
    expect($materials)->toHaveCount(2);

    // Each material carries its quantization array verbatim under both keys;
    // verify shape + boundary samples rather than echoing back all 8 ints.
    $byKey = collect($materials)->keyBy('key');

    $goldEntry = $byKey->get('Ore_Gold');
    expect($goldEntry['quality_quantization'])->toBe($goldQuantization)
        ->and($goldEntry['quality_quantized_values'])->toBe($goldQuantization)
        ->and($goldEntry['quality_quantization'][0])->toBe(318)
        ->and($goldEntry['quality_quantization'])->toHaveCount(8);

    $aluminumEntry = $byKey->get('Ore_Aluminum');
    expect($aluminumEntry['quality_quantization'])->toBe($aluminumQuantization)
        ->and($aluminumEntry['quality_quantized_values'])->toBe($aluminumQuantization)
        ->and($aluminumEntry['quality_quantization'][0])->toBe(300)
        ->and($aluminumEntry['quality_quantization'])->toHaveCount(8);
});
