<?php

declare(strict_types=1);

use App\Support\Seo\BlueprintShowSeoData;
use App\Support\Seo\CommodityShowSeoData;
use App\Support\Seo\ItemShowSeoData;
use App\Support\Seo\MissionShowSeoData;
use App\Support\Seo\StarmapLocationShowSeoData;
use App\Support\Seo\VehicleShowSeoData;
use Illuminate\Http\Request;

it('builds vehicle seo data with session version fallback and shared schema helpers', function (): void {
    $request = Request::create('/vehicles');
    $request->setLaravelSession(app('session.store'));
    $request->session()->put('game_version_code', '4.1.0-LIVE');

    $vehicleUuid = 'veh-123';
    $seo = app(VehicleShowSeoData::class)->build([
        'uuid' => $vehicleUuid,
        'name' => 'Mercury Star Runner',
        'manufacturer' => [
            'name' => 'Crusader Industries',
            'code' => 'CRUS',
        ],
        'size_class' => 3,
        'career' => 'Transport',
        'role' => 'Courier',
        'class_name' => 'Medium Freight',
        'description' => [
            'en_EN' => 'Fast &amp; versatile courier ship.',
        ],
        'crew' => ['min' => 2],
        'cargo_capacity' => 114,
        'speed' => ['scm' => 215, 'max' => 1287],
        'quantum' => ['quantum_speed' => 250_000_000],
    ], $request);

    expect($seo['title'])->toBe('Mercury Star Runner by Crusader Industries | Size 3 Courier Vehicle | Star Citizen')
        ->and($seo['metaDescription'])->toBe('Fast & versatile courier ship.')
        ->and($seo['canonicalUrl'])->toBe(route('web.vehicles.show', [
            'vehicle' => $vehicleUuid,
            'version' => '4.1.0-LIVE',
        ]))
        ->and($seo['keywords'])->toContain('Star Citizen', 'SC', 'Size 3')
        ->and($seo['breadcrumbs'])->toHaveCount(3)
        ->and($seo['breadcrumbs'][0]['url'])->toBe(route('web.vehicles.index', ['version' => '4.1.0-LIVE']))
        ->and(data_get($seo, 'structuredData.0.@type'))->toBe('BreadcrumbList')
        ->and(data_get($seo, 'structuredData.1.@type'))->toBe('Vehicle')
        ->and(data_get($seo, 'structuredData.1.vehicleConfiguration'))->toBe('Medium Freight')
        ->and(data_get($seo, 'structuredData.1.additionalProperty'))->toHaveCount(9);
});

it('builds item seo data with localized description and type-specific breadcrumbs', function (): void {
    $request = Request::create('/items');
    $request->setLaravelSession(app('session.store'));
    $request->session()->put('game_version_code', '4.1.0-LIVE');

    $itemUuid = 'item-123';
    $seo = app(ItemShowSeoData::class)->build([
        'uuid' => $itemUuid,
        'name' => 'Voyager Cooler',
        'type' => 'Cooler',
        'manufacturer' => ['name' => 'Klaus &amp; Werner'],
        'classification' => 'Ship.Cooler',
        'class' => 'Military',
        'grade' => '1',
        'description' => [
            'en' => 'Military &amp; tuned cooler.',
        ],
        'size' => 2,
    ], $request);

    expect($seo['title'])->toBe('Voyager Cooler by Klaus & Werner | Cooler Size 2 Military Grade A | Star Citizen')
        ->and($seo['metaDescription'])->toBe('Military & tuned cooler.')
        ->and($seo['canonicalUrl'])->toBe(route('web.items.show', [
            'item' => $itemUuid,
            'version' => '4.1.0-LIVE',
        ]))
        ->and($seo['breadcrumbs'])->toHaveCount(5)
        ->and($seo['breadcrumbs'][1]['label'])->toBe('Vehicle Items')
        ->and($seo['breadcrumbs'][2]['label'])->toBe('Components')
        ->and($seo['breadcrumbs'][3]['label'])->toBe('Coolers')
        ->and(data_get($seo, 'structuredData.0.@type'))->toBe('BreadcrumbList')
        ->and(data_get($seo, 'structuredData.1.@type'))->toBe('Item')
        ->and(data_get($seo, 'structuredData.1.brand.name'))->toBe('Klaus & Werner')
        ->and(data_get($seo, 'structuredData.1.additionalProperty'))->toHaveCount(5);
});

it('builds starmap seo data with query-only version handling and identifier fallback routing', function (): void {
    $request = Request::create('/locations?version=4.1.0-LIVE');
    $request->setLaravelSession(app('session.store'));
    $request->session()->put('game_version_code', '4.0.0-PTU');

    $locationUuid = 'loc-123';
    $starUuid = 'star-123';
    $parentUuid = 'planet-123';
    $seo = app(StarmapLocationShowSeoData::class)->build([
        'uuid' => $locationUuid,
        'slug' => 'port-tressler',
        'name' => 'Port Tressler',
        'Type' => [
            'Name' => 'Station',
            'Classification' => 'Orbital',
        ],
        'description' => 'Orbital logistics hub.',
        'star' => [
            'name' => 'Stanton',
            'uuid' => $starUuid,
            'slug' => 'stanton-star',
        ],
        'parent' => [
            'name' => 'microTech',
            'uuid' => $parentUuid,
            'slug' => 'microtech',
            'type_name' => 'Planet',
        ],
        'Affiliation' => ['Name' => 'UEE'],
        'Jurisdiction' => ['Name' => 'Civilian'],
        'child_count' => '3',
        'amenities' => [
            ['name' => 'Clinic', 'display_name' => 'Clinic'],
            ['name' => 'Refuel', 'display_name' => 'Refuel'],
        ],
    ], $request);

    expect($seo['canonicalUrl'])->toBe(route('web.locations.show', [
        'identifier' => 'port-tressler',
        'version' => '4.1.0-LIVE',
    ]))
        ->and($seo['title'])->toBe('Port Tressler | Station | Star Citizen Starmap')
        ->and($seo['metaDescription'])->toBe('Orbital logistics hub.')
        ->and($seo['breadcrumbs'])->toHaveCount(4)
        ->and($seo['breadcrumbs'][0]['url'])->toBe(route('web.locations.index', ['version' => '4.1.0-LIVE']))
        ->and($seo['breadcrumbs'][1]['url'])->toBe(route('web.locations.show', [
            'identifier' => 'stanton-star',
            'version' => '4.1.0-LIVE',
        ]))
        ->and(data_get($seo, 'structuredData.0.@type'))->toBe('BreadcrumbList')
        ->and(data_get($seo, 'structuredData.1.@type'))->toBe('Place')
        ->and(data_get($seo, 'structuredData.1.additionalProperty'))->toHaveCount(5)
        ->and(data_get($seo, 'structuredData.1.containedInPlace.name'))->toBe('microTech')
        ->and(data_get($seo, 'structuredData.1.containedInPlace.containedInPlace.name'))->toBe('Stanton')
        ->and(data_get($seo, 'structuredData.1.amenityFeature'))->toHaveCount(2)
        ->and(data_get($seo, 'structuredData.1.amenityFeature.0.name'))->toBe('Clinic')
        ->and(data_get($seo, 'structuredData.1.amenityFeature.1.name'))->toBe('Refuel');
});

it('builds blueprint seo data with HowTo structured data and ingredients as supplies', function (): void {
    $request = Request::create('/blueprints');
    $request->setLaravelSession(app('session.store'));
    $request->session()->put('game_version_code', '4.1.0-LIVE');

    $blueprintUuid = 'bp-123';
    $seo = app(BlueprintShowSeoData::class)->build([
        'uuid' => $blueprintUuid,
        'output_name' => 'Doom Missile',
        'output_class' => 'Military',
        'output' => [
            'type' => 'Missile',
            'subtype' => 'Guided',
        ],
        'craft_time_seconds' => 120,
        'ingredient_count' => 3,
        'ingredients' => [
            ['name' => 'Steel', 'quantity' => 5],
            ['name' => 'Copper', 'quantity' => 3],
            ['name' => 'Carbon', 'quantity' => 2],
        ],
    ], $request);

    expect($seo['title'])->toBe('Doom Missile Blueprint')
        ->and($seo['metaDescription'])->toContain('Doom Missile blueprint')
        ->and($seo['canonicalUrl'])->toBe(route('web.blueprints.show', [
            'blueprint' => $blueprintUuid,
            'version' => '4.1.0-LIVE',
        ]))
        ->and($seo['keywords'])->toContain('Doom Missile', 'Missile', 'Military', 'Blueprint', 'Star Citizen')
        ->and($seo['breadcrumbs'])->toHaveCount(2)
        ->and($seo['breadcrumbs'][0]['label'])->toBe('All Blueprints')
        ->and($seo['breadcrumbs'][0]['url'])->toBe(route('web.blueprints.index', ['version' => '4.1.0-LIVE']))
        ->and(data_get($seo, 'structuredData.0.@type'))->toBe('BreadcrumbList')
        ->and(data_get($seo, 'structuredData.1.@type'))->toBe('HowTo')
        ->and(data_get($seo, 'structuredData.1.name'))->toBe('Doom Missile Blueprint')
        ->and(data_get($seo, 'structuredData.1.totalTime'))->toBe('PT120S')
        ->and(data_get($seo, 'structuredData.1.supply'))->toHaveCount(3)
        ->and(data_get($seo, 'structuredData.1.supply.0.name'))->toBe('Steel')
        ->and(data_get($seo, 'structuredData.1.step.@type'))->toBe('HowToStep')
        ->and(data_get($seo, 'structuredData.1.step.name'))->toBe('Craft Doom Missile')
        ->and(data_get($seo, 'structuredData.1.additionalProperty'))->toHaveCount(2);
});

it('builds empty blueprint seo data with noindex robots and no structured data', function (): void {
    $request = Request::create('/blueprints/search');
    $request->setLaravelSession(app('session.store'));
    $request->session()->put('game_version_code', '4.1.0-LIVE');

    $seo = app(BlueprintShowSeoData::class)->build([], $request, isEmptyMode: true);

    expect($seo['title'])->toBe('Search Blueprints - Star Citizen')
        ->and($seo['metaDescription'])->toBe('Search Star Citizen blueprints by output name, class, item, or input resource.')
        ->and($seo['keywords'])->toBe([])
        ->and($seo['structuredData'])->toBe([])
        ->and($seo['robots'])->toBe('noindex,follow')
        ->and($seo['canonicalUrl'])->toBe(route('web.blueprints.search', ['version' => '4.1.0-LIVE']));
});

it('builds mission seo data with faction breadcrumbs and Action structured data', function (): void {
    $request = Request::create('/missions');
    $request->setLaravelSession(app('session.store'));
    $request->session()->put('game_version_code', '4.1.0-LIVE');

    $missionSlug = 'nine-tails-heist';
    $seo = app(MissionShowSeoData::class)->build([
        'uuid' => 'mis-456',
        'title' => 'Nine Tails Heist',
        'mission_type' => 'Delivery',
        'mission_giver' => 'Ruto',
        'description' => 'Deliver cargo through dangerous space.',
        'faction' => [
            'name' => 'Nine Tails',
            'uuid' => 'fac-789',
        ],
        'legality_label' => 'Illegal',
        'has_combat' => true,
        'enemy_count_min' => 3,
        'enemy_count_max' => 5,
        'rank_index' => 2,
        'time_to_complete_minutes' => 45,
        'reputation_amount' => 1500,
        'game_version' => '4.1.0-LIVE',
        'web_url' => route('web.missions.show', ['mission' => $missionSlug, 'version' => '4.1.0-LIVE']),
    ], $request);

    expect($seo['title'])->toBe('Nine Tails Heist | Delivery | Star Citizen Mission')
        ->and($seo['metaDescription'])->toBe('Deliver cargo through dangerous space.')
        ->and($seo['canonicalUrl'])->toBe(route('web.missions.show', [
            'mission' => $missionSlug,
            'version' => '4.1.0-LIVE',
        ]))
        ->and($seo['keywords'])->toContain('Nine Tails Heist', 'Delivery', 'Nine Tails', 'Ruto', 'Illegal', 'Star Citizen', 'SC')
        ->and($seo['breadcrumbs'])->toHaveCount(3)
        ->and($seo['breadcrumbs'][0]['label'])->toBe('All Missions')
        ->and($seo['breadcrumbs'][1]['label'])->toBe('Nine Tails')
        ->and($seo['breadcrumbs'][1]['url'])->toBe(route('web.missions.index', [
            'version' => '4.1.0-LIVE',
            'filter' => ['faction' => 'Nine Tails'],
        ]))
        ->and(data_get($seo, 'structuredData.0.@type'))->toBe('BreadcrumbList')
        ->and(data_get($seo, 'structuredData.1.@type'))->toBe('Action')
        ->and(data_get($seo, 'structuredData.1.agent.name'))->toBe('Nine Tails')
        ->and(data_get($seo, 'structuredData.1.additionalProperty'))->toHaveCount(9);
});

it('builds mission seo data without faction and with fallback description', function (): void {
    $request = Request::create('/missions');
    $request->setLaravelSession(app('session.store'));
    $request->session()->put('game_version_code', '4.0.0-LIVE');

    $missionSlug = 'generic-mission';
    $seo = app(MissionShowSeoData::class)->build([
        'uuid' => 'mis-789',
        'title' => 'Generic Mission',
        'mission_type' => 'Bounty',
        'description' => null,
        'faction' => null,
        'legality_label' => 'Legal',
        'reputation_amount' => 500,
        'has_combat' => false,
        'web_url' => route('web.missions.show', ['mission' => $missionSlug, 'version' => '4.0.0-LIVE']),
    ], $request);

    expect($seo['title'])->toBe('Generic Mission | Bounty | Star Citizen Mission')
        ->and($seo['metaDescription'])->toContain('Bounty')
        ->and($seo['metaDescription'])->toContain('500 reputation XP')
        ->and($seo['canonicalUrl'])->toBe(route('web.missions.show', [
            'mission' => $missionSlug,
            'version' => '4.0.0-LIVE',
        ]))
        ->and($seo['breadcrumbs'])->toHaveCount(2)
        ->and($seo['breadcrumbs'][0]['label'])->toBe('All Missions')
        ->and($seo['breadcrumbs'][1]['label'])->toBe('Generic Mission')
        ->and(data_get($seo, 'structuredData.1.agent'))->toBeNull()
        ->and(data_get($seo, 'structuredData.1.additionalProperty'))->toHaveCount(3);
});

it('builds commodity seo data with breadcrumbs and Item structured data', function (): void {
    $request = Request::create('/commodities');
    $request->setLaravelSession(app('session.store'));
    $request->session()->put('game_version_code', '4.1.0-LIVE');

    $commodityUuid = 'com-123';
    $commoditySlug = 'agricium';
    $seo = app(CommodityShowSeoData::class)->build([
        'uuid' => $commodityUuid,
        'slug' => $commoditySlug,
        'name' => 'Agricium',
        'description' => 'A rare and valuable mineral.',
        'kind' => 'Mineral',
        'tier' => 2,
        'density' => 8.5,
        'instability' => 0.25,
        'resistance' => -0.5,
        'raw_versions' => [],
        'refined_version' => [
            'name' => 'Refined Agricium',
            'web_url' => 'https://example.com/commodities/refined-123',
        ],
    ], $request);

    expect($seo['title'])->toBe('Agricium | Mineral Tier 2 | Star Citizen')
        ->and($seo['metaDescription'])->toBe('A rare and valuable mineral.')
        ->and($seo['canonicalUrl'])->toBe(route('web.commodities.show', [
            'identifier' => $commoditySlug,
            'version' => '4.1.0-LIVE',
        ]))
        ->and($seo['keywords'])->toContain('Agricium', 'Mineral', 'Tier 2', 'Star Citizen', 'SC')
        ->and($seo['breadcrumbs'])->toHaveCount(3)
        ->and($seo['breadcrumbs'][0]['label'])->toBe('All Commodities')
        ->and($seo['breadcrumbs'][0]['url'])->toBe(route('web.commodities.index', ['version' => '4.1.0-LIVE']))
        ->and($seo['breadcrumbs'][1]['label'])->toBe('Agricium')
        ->and($seo['breadcrumbs'][2]['label'])->toBe('Refined Agricium')
        ->and($seo['breadcrumbs'][2]['url'])->toBe('https://example.com/commodities/refined-123')
        ->and(data_get($seo, 'structuredData.0.@type'))->toBe('BreadcrumbList')
        ->and(data_get($seo, 'structuredData.1.@type'))->toBe('Item')
        ->and(data_get($seo, 'structuredData.1.name'))->toBe('Agricium')
        ->and(data_get($seo, 'structuredData.1.sku'))->toBe($commodityUuid)
        ->and(data_get($seo, 'structuredData.1.additionalProperty'))->toHaveCount(5);
});

it('builds commodity seo data with raw version breadcrumb and fallback description', function (): void {
    $request = Request::create('/commodities');

    $commodityUuid = 'com-456';
    $commoditySlug = 'quantanium';
    $seo = app(CommodityShowSeoData::class)->build([
        'uuid' => $commodityUuid,
        'slug' => $commoditySlug,
        'name' => 'Quantanium',
        'kind' => 'Mineral',
        'tier' => 3,
        'raw_versions' => [
            ['name' => 'Raw Quantanium', 'web_url' => 'https://example.com/commodities/raw-456'],
        ],
    ], $request);

    expect($seo['title'])->toBe('Quantanium | Mineral Tier 3 | Star Citizen')
        ->and($seo['metaDescription'])->toContain('Quantanium')
        ->and($seo['metaDescription'])->toContain('Mineral')
        ->and($seo['canonicalUrl'])->toBe(route('web.commodities.show', [
            'identifier' => $commoditySlug,
        ]))
        ->and($seo['breadcrumbs'])->toHaveCount(3)
        ->and($seo['breadcrumbs'][1]['label'])->toBe('Raw Quantanium')
        ->and($seo['breadcrumbs'][2]['label'])->toBe('Quantanium')
        ->and(data_get($seo, 'structuredData.1.additionalProperty'))->toHaveCount(2);
});
