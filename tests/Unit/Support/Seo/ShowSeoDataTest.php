<?php

declare(strict_types=1);

use App\Support\Seo\ItemShowSeoData;
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
        ->and(data_get($seo, 'structuredData.1.@type'))->toBe('Product')
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
        'name' => 'Port Tressler',
        'Type' => [
            'Name' => 'Station',
            'Classification' => 'Orbital',
        ],
        'description' => 'Orbital logistics hub.',
        'star' => [
            'name' => 'Stanton',
            'uuid' => $starUuid,
        ],
        'parent' => [
            'name' => 'microTech',
            'uuid' => $parentUuid,
        ],
        'Affiliation' => ['Name' => 'UEE'],
        'Jurisdiction' => ['Name' => 'Civilian'],
        'child_count' => '3',
    ], $request);

    expect($seo['canonicalUrl'])->toBe(route('web.locations.show', [
        'identifier' => $locationUuid,
        'version' => '4.1.0-LIVE',
    ]))
        ->and($seo['title'])->toBe('Port Tressler | Station | Star Citizen Starmap')
        ->and($seo['metaDescription'])->toBe('Orbital logistics hub.')
        ->and($seo['breadcrumbs'])->toHaveCount(4)
        ->and($seo['breadcrumbs'][0]['url'])->toBe(route('web.locations.index', ['version' => '4.1.0-LIVE']))
        ->and($seo['breadcrumbs'][1]['url'])->toBe(route('web.locations.show', [
            'identifier' => $starUuid,
            'version' => '4.1.0-LIVE',
        ]))
        ->and(data_get($seo, 'structuredData.0.@type'))->toBe('BreadcrumbList')
        ->and(data_get($seo, 'structuredData.1.@type'))->toBe('Place')
        ->and(data_get($seo, 'structuredData.1.additionalProperty'))->toHaveCount(5);
});
