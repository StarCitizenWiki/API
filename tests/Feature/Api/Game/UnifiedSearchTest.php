<?php

declare(strict_types=1);

use App\Models\Game\Blueprint;
use App\Models\Game\BlueprintData;
use App\Models\Game\Commodity\Commodity;
use App\Models\Game\GameVersion;
use App\Models\Game\Item;
use App\Models\Game\ItemData;
use App\Models\Game\Manufacturer;
use App\Models\Game\Mission\Mission;
use App\Models\Game\Mission\MissionData;
use App\Models\Game\StarmapLocation;
use App\Models\Game\StarmapLocationData;
use App\Models\Game\Vehicle;
use App\Models\Game\VehicleData;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns grouped results for a query matching multiple domains', function (): void {
    $version = GameVersion::factory()->create([
        'code' => '1.0.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $manufacturer = Manufacturer::factory()->create();

    // Item
    $item = Item::factory()->create(['slug' => 'arrow-item']);
    ItemData::factory()
        ->for($item)
        ->for($version, 'gameVersion')
        ->for($manufacturer)
        ->create([
            'name' => 'Arrow Sniper Rifle',
            'class_name' => 'Arrow_Sniper',
            'classification' => 'FPS.Weapon.Sniper',
            'data' => [],
        ]);

    // Vehicle
    $vehicle = Vehicle::factory()->create(['slug' => 'arrow-vehicle']);
    VehicleData::factory()
        ->for($vehicle)
        ->for($version, 'gameVersion')
        ->for($manufacturer)
        ->create([
            'name' => 'Arrow Fighter',
            'class_name' => 'AEGS_Arrow',
        ]);

    // Location
    $location = StarmapLocation::factory()->create();
    StarmapLocationData::factory()
        ->for($location, 'location')
        ->for($version, 'gameVersion')
        ->create([
            'name' => 'Arrow Ridge Outpost',
            'system' => 'Stanton',
            'type_name' => 'Outpost',
        ]);

    // Commodity
    Commodity::factory()->create([
        'name' => 'Arrowroot Extract',
        'key' => 'arrow_commodity',
        'slug' => 'arrowroot-extract',
    ]);

    // Blueprint
    $blueprint = Blueprint::factory()->create(['slug' => 'arrow-blueprint']);
    BlueprintData::factory()
        ->for($blueprint)
        ->for($version, 'gameVersion')
        ->create([
            'output_name' => 'Arrow Weapon Blueprint',
            'output_class' => 'BP_CRAFT_Arrow',
            'key' => 'arrow_bp_key',
        ]);

    // Mission
    $mission = Mission::factory()->create(['slug' => 'arrow-mission']);
    MissionData::factory()
        ->for($mission)
        ->for($version, 'gameVersion')
        ->create([
            'title' => 'Arrow Retrieval',
            'mission_type' => 'Delivery',
            'not_for_release' => false,
            'work_in_progress' => false,
        ]);

    $response = $this->getJson('/api/search?filter[query]=Arrow');

    $response->assertSuccessful();

    $types = collect($response->json('data'))->pluck('type');

    expect($types)->toContain('items', 'vehicles', 'locations', 'commodities', 'blueprints', 'missions');

    $groups = collect($response->json('data'));

    // Item: classification_label resolved via ItemFilterLabel
    $itemsGroup = $groups->first(fn ($g) => $g['type'] === 'items');
    expect($itemsGroup['results'][0]['classification_label'])->toBe('Sniper');

    // Vehicle: classification is null → classification_label is null
    $vehiclesGroup = $groups->first(fn ($g) => $g['type'] === 'vehicles');
    expect($vehiclesGroup['results'][0]['classification_label'])->toBeNull();

    // Location: classification_label mirrors type_name
    $locationsGroup = $groups->first(fn ($g) => $g['type'] === 'locations');
    expect($locationsGroup['results'][0]['classification_label'])->toBe('Outpost');

    // Commodity: classification is null → classification_label is null
    $commoditiesGroup = $groups->first(fn ($g) => $g['type'] === 'commodities');
    expect($commoditiesGroup['results'][0]['classification_label'])->toBeNull();

    // Blueprint: classification is null → classification_label is null
    $blueprintsGroup = $groups->first(fn ($g) => $g['type'] === 'blueprints');
    expect($blueprintsGroup['results'][0]['name'])->toBe('Arrow Weapon Blueprint');
    expect($blueprintsGroup['results'][0]['classification_label'])->toBeNull();

    // Mission: classification_label mirrors mission_type
    $missionsGroup = $groups->first(fn ($g) => $g['type'] === 'missions');
    expect($missionsGroup['results'][0]['classification_label'])->toBe('Delivery');
});

it('returns 422 for missing query', function (): void {
    GameVersion::factory()->create([
        'code' => '1.0.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $this->getJson('/api/search')
        ->assertStatus(422);
});

it('returns 422 for query shorter than 2 characters', function (): void {
    GameVersion::factory()->create([
        'code' => '1.0.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $this->getJson('/api/search?filter[query]=A')
        ->assertStatus(422);
});

it('omits groups with zero results', function (): void {
    $version = GameVersion::factory()->create([
        'code' => '1.0.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $manufacturer = Manufacturer::factory()->create();

    // Only create an item
    $item = Item::factory()->create(['slug' => 'unique-item']);
    ItemData::factory()
        ->for($item)
        ->for($version, 'gameVersion')
        ->for($manufacturer)
        ->create([
            'name' => 'UniqueItemNameOnly',
            'class_name' => 'unique_item_class',
            'classification' => 'Test',
            'data' => [],
        ]);

    $response = $this->getJson('/api/search?filter[query]=UniqueItemNameOnly');

    $response->assertSuccessful();

    $types = collect($response->json('data'))->pluck('type');

    expect($types)->toContain('items');
    expect($types)->not->toContain('vehicles', 'locations', 'commodities', 'blueprints', 'missions');
});

it('respects version parameter', function (): void {
    $v1 = GameVersion::factory()->create([
        'code' => '1.0.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $v2 = GameVersion::factory()->create([
        'code' => '2.0.0-LIVE',
        'channel' => 'live',
        'is_default' => false,
        'released_at' => now(),
    ]);

    $manufacturer = Manufacturer::factory()->create();

    // Item only in v1
    $item = Item::factory()->create(['slug' => 'v1-item']);
    ItemData::factory()
        ->for($item)
        ->for($v1, 'gameVersion')
        ->for($manufacturer)
        ->create([
            'name' => 'VersionTest Item',
            'class_name' => 'version_test_item',
            'classification' => 'Test',
            'data' => [],
        ]);

    // Item only in v2
    $item2 = Item::factory()->create(['slug' => 'v2-item']);
    ItemData::factory()
        ->for($item2)
        ->for($v2, 'gameVersion')
        ->for($manufacturer)
        ->create([
            'name' => 'VersionTest Item V2',
            'class_name' => 'version_test_item_v2',
            'classification' => 'Test',
            'data' => [],
        ]);

    // Default version (v1) should only find v1 item
    $defaultResponse = $this->getJson('/api/search?filter[query]=VersionTest');
    $defaultResponse->assertSuccessful();
    $defaultNames = collect($defaultResponse->json('data'))
        ->filter(fn ($group) => $group['type'] === 'items')
        ->pluck('results')
        ->flatten(1)
        ->pluck('name');
    expect($defaultNames)->toContain('VersionTest Item');
    expect($defaultNames)->not->toContain('VersionTest Item V2');

    // Explicit v2 should find v2 item
    $v2Response = $this->getJson('/api/search?filter[query]=VersionTest&version=2.0.0-LIVE');
    $v2Response->assertSuccessful();
    $v2Names = collect($v2Response->json('data'))
        ->filter(fn ($group) => $group['type'] === 'items')
        ->pluck('results')
        ->flatten(1)
        ->pluck('name');
    expect($v2Names)->toContain('VersionTest Item V2');
    expect($v2Names)->not->toContain('VersionTest Item');
});

it('limits each group to max 5 results', function (): void {
    $version = GameVersion::factory()->create([
        'code' => '1.0.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $manufacturer = Manufacturer::factory()->create();

    // Create 7 items with matching name
    for ($i = 0; $i < 7; $i++) {
        $item = Item::factory()->create(['slug' => "overflow-item-{$i}"]);
        ItemData::factory()
            ->for($item)
            ->for($version, 'gameVersion')
            ->for($manufacturer)
            ->create([
                'name' => "OverflowTest Item {$i}",
                'class_name' => "overflow_test_{$i}",
                'classification' => 'Test',
                'data' => [],
            ]);
    }

    $response = $this->getJson('/api/search?filter[query]=OverflowTest');

    $response->assertSuccessful();

    $itemsGroup = collect($response->json('data'))->first(fn ($group) => $group['type'] === 'items');

    expect($itemsGroup['results'])->toHaveCount(5);
});

it('generates correct web_url per domain', function (): void {
    $version = GameVersion::factory()->create([
        'code' => '1.0.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $manufacturer = Manufacturer::factory()->create();

    $item = Item::factory()->create(['slug' => 'url-test-item']);
    ItemData::factory()
        ->for($item)
        ->for($version, 'gameVersion')
        ->for($manufacturer)
        ->create([
            'name' => 'UrlTestItem',
            'class_name' => 'url_test_item',
            'classification' => 'Test',
            'data' => [],
        ]);

    $vehicle = Vehicle::factory()->create(['slug' => 'url-test-vehicle']);
    VehicleData::factory()
        ->for($vehicle)
        ->for($version, 'gameVersion')
        ->for($manufacturer)
        ->create([
            'name' => 'UrlTestVehicle',
            'class_name' => 'url_test_vehicle',
        ]);

    $blueprint = Blueprint::factory()->create(['slug' => 'url-test-blueprint']);
    BlueprintData::factory()
        ->for($blueprint)
        ->for($version, 'gameVersion')
        ->create([
            'output_name' => 'UrlTestBlueprint',
            'output_class' => 'BP_UrlTest',
            'key' => 'url_test_bp_key',
        ]);

    $response = $this->getJson('/api/search?filter[query]=UrlTest');

    $response->assertSuccessful();

    $groups = collect($response->json('data'));
    $itemsGroup = $groups->first(fn ($g) => $g['type'] === 'items');
    $vehiclesGroup = $groups->first(fn ($g) => $g['type'] === 'vehicles');
    $blueprintsGroup = $groups->first(fn ($g) => $g['type'] === 'blueprints');

    expect($itemsGroup['results'][0]['web_url'])->toEndWith('/items/url-test-item');
    expect($vehiclesGroup['results'][0]['web_url'])->toEndWith('/vehicles/url-test-vehicle');
    expect($blueprintsGroup['results'][0]['web_url'])->toEndWith('/blueprints/url-test-blueprint');

    // api_url uses API route names
    expect($itemsGroup['results'][0]['api_url'])->toEndWith('/api/items/url-test-item');
    expect($vehiclesGroup['results'][0]['api_url'])->toEndWith('/api/vehicles/url-test-vehicle');
    expect($blueprintsGroup['results'][0]['api_url'])->toEndWith('/api/blueprints/url-test-blueprint');
});

it('returns empty data array when nothing matches', function (): void {
    GameVersion::factory()->create([
        'code' => '1.0.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $response = $this->getJson('/api/search?filter[query]=ZZZZZZZZZZZ');

    $response->assertSuccessful()
        ->assertJson(['data' => []]);
});
