<?php

declare(strict_types=1);

use App\Models\Game\Commodity\Commodity;
use App\Models\Game\GameVersion;
use App\Models\Game\Item;
use App\Models\Game\ItemData;
use App\Models\Game\Manufacturer;
use App\Models\Game\Vehicle;
use App\Models\Game\VehicleData;

beforeEach(function (): void {
    $this->gameVersion = GameVersion::factory()->create([
        'code' => '1.0.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $this->manufacturer = Manufacturer::factory()->create();
});

describe('meta.resource on item show', function (): void {
    it('includes canonical resource metadata', function (): void {
        $item = Item::factory()->create(['slug' => 'test-meta-item']);

        ItemData::factory()
            ->for($item)
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Meta Test Item',
                'class_name' => 'meta_test_item',
                'classification' => 'Test',
                'data' => ['stdItem' => []],
            ]);

        $response = $this->getJson("/api/items/{$item->uuid}");

        $response->assertSuccessful()
            ->assertJsonPath('meta.resource.type', 'item')
            ->assertJsonPath('meta.resource.uuid', $item->uuid)
            ->assertJsonPath('meta.resource.slug', 'test-meta-item')
            ->assertJsonPath('meta.resource.version', '1.0.0-LIVE');

        $apiUrl = $response->json('meta.resource.api_url');
        expect($apiUrl)->toContain('/api/items/');

        $webUrl = $response->json('meta.resource.web_url');
        expect($webUrl)->toContain('/items/test-meta-item');
    });

    it('includes version in canonical api_url when version query is set', function (): void {
        $item = Item::factory()->create(['slug' => 'versioned-meta-item']);

        ItemData::factory()
            ->for($item)
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Versioned Meta Item',
                'class_name' => 'versioned_meta_item',
                'classification' => 'Test',
                'data' => ['stdItem' => []],
            ]);

        $response = $this->getJson("/api/items/{$item->uuid}?version=1.0.0-LIVE");

        $response->assertSuccessful();

        $apiUrl = $response->json('meta.resource.api_url');
        expect($apiUrl)->toContain('version=1.0.0-LIVE');

        $version = $response->json('meta.resource.version');
        expect($version)->toBe('1.0.0-LIVE');
    });
});

describe('meta.resource on vehicle show', function (): void {
    it('includes canonical resource metadata', function (): void {
        $vehicle = Vehicle::factory()->create(['slug' => 'test-meta-vehicle']);

        VehicleData::factory()
            ->for($vehicle)
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Meta Test Vehicle',
                'display_name' => 'Meta Test Vehicle',
                'class_name' => 'AEGS_MetaTestVehicle',
            ]);

        $response = $this->getJson("/api/vehicles/{$vehicle->uuid}");

        $response->assertSuccessful()
            ->assertJsonPath('meta.resource.type', 'vehicle')
            ->assertJsonPath('meta.resource.uuid', $vehicle->uuid)
            ->assertJsonPath('meta.resource.slug', 'test-meta-vehicle')
            ->assertJsonPath('meta.resource.version', '1.0.0-LIVE');

        $apiUrl = $response->json('meta.resource.api_url');
        expect($apiUrl)->toContain('/api/vehicles/');

        $webUrl = $response->json('meta.resource.web_url');
        expect($webUrl)->toContain('/vehicles/test-meta-vehicle');
    });
});

describe('item-to-vehicle redirect preserves query params', function (): void {
    it('preserves locale and include on redirect from items to vehicles', function (): void {
        $item = Item::factory()->create();
        Vehicle::factory()->create(['uuid' => $item->uuid]);

        ItemData::factory()
            ->for($item)
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Redirect Vehicle Item',
                'class_name' => 'redirect_vehicle_item',
                'classification' => 'Vehicle',
                'data' => ['stdItem' => []],
            ]);

        $response = $this->getJson("/api/items/{$item->uuid}?locale=en_EN&include=hardpoints");

        $response->assertRedirect();

        $location = $response->headers->get('Location');
        expect($location)->toContain("/api/vehicles/{$item->uuid}");

        parse_str((string) parse_url((string) $location, PHP_URL_QUERY), $params);
        expect($params)->toMatchArray([
            'locale' => 'en_EN',
            'include' => 'hardpoints',
        ]);
    });
});

describe('meta.resource after following search redirect', function (): void {
    it('exposes canonical metadata in final item response', function (): void {
        $item = Item::factory()->create(['slug' => 'search-meta-item']);

        ItemData::factory()
            ->for($item)
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'SearchMetaItem',
                'class_name' => 'search_meta_item',
                'classification' => 'Test',
                'data' => ['stdItem' => []],
            ]);

        // Follow redirect from search to items
        $redirectResponse = $this->getJson('/api/search/SearchMetaItem');
        $redirectResponse->assertStatus(302);

        $location = $redirectResponse->headers->get('Location');
        $finalResponse = $this->getJson($location);
        $finalResponse->assertSuccessful()
            ->assertJsonPath('meta.resource.type', 'item')
            ->assertJsonPath('meta.resource.uuid', $item->uuid)
            ->assertJsonPath('meta.resource.slug', 'search-meta-item')
            ->assertJsonPath('meta.resource.version', '1.0.0-LIVE');
    });

    it('exposes canonical metadata in final vehicle response', function (): void {
        $vehicle = Vehicle::factory()->create(['slug' => 'search-meta-vehicle']);

        VehicleData::factory()
            ->for($vehicle)
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'SearchMetaVehicle',
                'display_name' => 'SearchMetaVehicle',
                'class_name' => 'AEGS_SearchMetaVehicle',
            ]);

        $redirectResponse = $this->getJson('/api/search/SearchMetaVehicle');
        $redirectResponse->assertStatus(302);

        $location = $redirectResponse->headers->get('Location');
        $finalResponse = $this->getJson($location);
        $finalResponse->assertSuccessful()
            ->assertJsonPath('meta.resource.type', 'vehicle')
            ->assertJsonPath('meta.resource.uuid', $vehicle->uuid)
            ->assertJsonPath('meta.resource.slug', 'search-meta-vehicle')
            ->assertJsonPath('meta.resource.version', '1.0.0-LIVE');
    });

    it('exposes canonical metadata in final commodity response', function (): void {
        $commodity = Commodity::factory()->create([
            'name' => 'Laranite',
            'key' => 'laranite',
            'slug' => 'laranite',
        ]);

        $redirectResponse = $this->getJson('/api/search/Laranite');
        $redirectResponse->assertStatus(302);

        $location = $redirectResponse->headers->get('Location');
        $finalResponse = $this->getJson($location);
        $finalResponse->assertSuccessful()
            ->assertJsonPath('meta.resource.type', 'commodity')
            ->assertJsonPath('meta.resource.uuid', $commodity->uuid)
            ->assertJsonPath('meta.resource.slug', 'laranite');
    });
});
