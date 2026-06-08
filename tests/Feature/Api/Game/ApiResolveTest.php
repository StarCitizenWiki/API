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
use App\Models\Game\Vehicle;
use App\Models\Game\VehicleData;

describe('apiResolve', function (): void {
    beforeEach(function (): void {
        $this->gameVersion = GameVersion::factory()->create([
            'code' => '1.0.0-LIVE',
            'channel' => 'live',
            'is_default' => true,
            'released_at' => now(),
        ]);

        $this->manufacturer = Manufacturer::factory()->create();
    });

    it('redirects to the item API URL', function (): void {
        $item = Item::factory()->create(['slug' => 'test-item']);
        ItemData::factory()
            ->for($item)
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Test Resolve Item',
                'class_name' => 'Test_Resolve_Item',
                'classification' => 'Test',
                'data' => [],
            ]);

        $this->getJson('/api/search/Test%20Resolve%20Item')
            ->assertStatus(302)
            ->assertRedirect(route('items.show', ['identifier' => 'test-item']));
    });

    it('redirects to the vehicle API URL', function (): void {
        $vehicle = Vehicle::factory()->create(['slug' => 'test-vehicle']);
        VehicleData::factory()
            ->for($vehicle)
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Test Resolve Vehicle',
                'display_name' => 'Test Resolve Vehicle',
                'class_name' => 'AEGS_Test_Vehicle',
            ]);

        $this->getJson('/api/search/Test%20Resolve%20Vehicle')
            ->assertStatus(302)
            ->assertRedirect(route('vehicles.show', ['vehicle' => 'test-vehicle']));
    });

    it('resolves by UUID and redirects to the typed API URL', function (): void {
        $item = Item::factory()->create([
            'slug' => 'uuid-item',
            'uuid' => '11111111-2222-3333-4444-555555555555',
        ]);
        ItemData::factory()
            ->for($item)
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'UUID Test Item',
                'class_name' => 'uuid_test_item',
                'classification' => 'Test',
                'data' => [],
            ]);

        $this->getJson('/api/search/11111111-2222-3333-4444-555555555555')
            ->assertStatus(302)
            ->assertRedirect(route('items.show', ['identifier' => 'uuid-item']));
    });

    it('preserves include and locale parameters on the redirect', function (): void {
        $item = Item::factory()->create(['slug' => 'include-test-item']);
        ItemData::factory()
            ->for($item)
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Include Test Item',
                'class_name' => 'include_test_item',
                'classification' => 'Test',
                'data' => [],
            ]);

        $response = $this->getJson("/api/search/{$item->uuid}?locale=en_EN&include=related_items,blueprints");

        $response->assertStatus(302);

        $location = $response->headers->get('Location');
        expect(strtok((string) $location, '?'))->toBe(route('items.show', ['identifier' => 'include-test-item']));

        parse_str((string) parse_url((string) $location, PHP_URL_QUERY), $params);
        expect($params)->toMatchArray([
            'locale' => 'en_EN',
            'include' => 'related_items,blueprints',
        ]);
    });

    it('preserves version parameter on the redirect and uses it for resolution', function (): void {
        $v1 = $this->gameVersion;
        $v2 = GameVersion::factory()->create([
            'code' => '2.0.0-LIVE',
            'channel' => 'live',
            'is_default' => false,
            'released_at' => now(),
        ]);

        $item = Item::factory()->create(['slug' => 'version-test-item']);
        ItemData::factory()
            ->for($item)
            ->for($v1, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Version Item V1',
                'class_name' => 'version_item_v1',
                'classification' => 'Test',
                'data' => [],
            ]);
        ItemData::factory()
            ->for($item)
            ->for($v2, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Version Item V2',
                'class_name' => 'version_item_v2',
                'classification' => 'Test',
                'data' => [],
            ]);

        $defaultResponse = $this->getJson("/api/search/{$item->uuid}");
        $defaultResponse->assertRedirect(route('items.show', ['identifier' => 'version-test-item']));

        $v2Response = $this->getJson("/api/search/{$item->uuid}?version=2.0.0-LIVE");
        $v2Response->assertStatus(302);

        $location = $v2Response->headers->get('Location');
        expect(strtok((string) $location, '?'))->toBe(route('items.show', ['identifier' => 'version-test-item']));

        parse_str((string) parse_url((string) $location, PHP_URL_QUERY), $params);
        expect($params)->toMatchArray(['version' => '2.0.0-LIVE']);
    });

    it('resolves vehicles by class name', function (): void {
        $vehicle = Vehicle::factory()->create(['slug' => 'class-name-vehicle']);
        VehicleData::factory()
            ->for($vehicle)
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Class Name Vehicle',
                'display_name' => 'Class Name Vehicle',
                'class_name' => 'AEGS_ClassNameVehicle',
            ]);

        $this->getJson('/api/search/AEGS_ClassNameVehicle')
            ->assertStatus(302)
            ->assertRedirect(route('vehicles.show', ['vehicle' => 'class-name-vehicle']));
    });

    it('treats percent characters as literal text for exact resolve matches', function (): void {
        $decoy = Vehicle::factory()->create(['slug' => 'wildcard-decoy-vehicle']);
        VehicleData::factory()
            ->for($decoy)
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'WildcardXVehicle',
                'display_name' => 'WildcardXVehicle',
                'class_name' => 'WildcardXVehicle',
            ]);

        $exact = Vehicle::factory()->create(['slug' => 'wildcard-exact-vehicle']);
        VehicleData::factory()
            ->for($exact)
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Wildcard%Vehicle',
                'display_name' => 'Wildcard%Vehicle',
                'class_name' => 'Wildcard_Percent_Vehicle',
            ]);

        $this->getJson('/api/search/'.rawurlencode('Wildcard%Vehicle'))
            ->assertStatus(302)
            ->assertRedirect(route('vehicles.show', ['vehicle' => 'wildcard-exact-vehicle']));
    });

    it('resolves commodities by name', function (): void {
        Commodity::factory()->create([
            'name' => 'Agricium',
            'key' => 'agricium',
            'slug' => 'agricium',
        ]);

        $this->getJson('/api/search/Agricium')
            ->assertStatus(302)
            ->assertRedirect(route('commodities.show', ['commodity' => 'agricium']));
    });

    it('resolves blueprints by output name', function (): void {
        $blueprint = Blueprint::factory()->create(['slug' => 'weapon-bp']);
        BlueprintData::factory()
            ->for($blueprint)
            ->for($this->gameVersion, 'gameVersion')
            ->create([
                'output_name' => 'Weapon Blueprint',
                'output_class' => 'BP_Weapon',
                'key' => 'weapon_bp_key',
            ]);

        $this->getJson('/api/search/'.rawurlencode('Weapon Blueprint'))
            ->assertStatus(302)
            ->assertRedirect(route('blueprints.show', ['blueprint' => 'weapon-bp']));
    });

    it('resolves missions by title', function (): void {
        $mission = Mission::factory()->create(['slug' => 'delivery-mission']);
        MissionData::factory()
            ->for($mission)
            ->for($this->gameVersion, 'gameVersion')
            ->create([
                'title' => 'Delivery Run',
                'mission_type' => 'Delivery',
                'not_for_release' => false,
                'work_in_progress' => false,
            ]);

        $this->getJson('/api/search/'.rawurlencode('Delivery Run'))
            ->assertStatus(302)
            ->assertRedirect(route('missions.show', ['mission' => 'delivery-mission']));
    });

    it('returns 404 when nothing matches', function (): void {
        $this->getJson('/api/search/ZZZZZZZZZ_NOTHING_MATCHES')
            ->assertStatus(404);
    });

    it('respects priority order: vehicles before items', function (): void {
        $item = Item::factory()->create(['slug' => 'priority-item']);
        ItemData::factory()
            ->for($item)
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'PriorityTest',
                'class_name' => 'priority_test_item',
                'classification' => 'Test',
                'data' => [],
            ]);

        $vehicle = Vehicle::factory()->create(['slug' => 'priority-vehicle']);
        VehicleData::factory()
            ->for($vehicle)
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'PriorityTest',
                'display_name' => 'PriorityTest',
                'class_name' => 'AEGS_PriorityTest',
            ]);

        $this->getJson('/api/search/PriorityTest')
            ->assertStatus(302)
            ->assertRedirect(route('vehicles.show', ['vehicle' => 'priority-vehicle']));
    });

    it('returns a redirect with a Location header', function (): void {
        $item = Item::factory()->create(['slug' => 'content-type-item']);
        ItemData::factory()
            ->for($item)
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'ContentType Item',
                'class_name' => 'content_type_item',
                'classification' => 'Test',
                'data' => [],
            ]);

        $response = $this->getJson('/api/search/ContentType%20Item');

        $response->assertStatus(302)
            ->assertRedirect(route('items.show', ['identifier' => 'content-type-item']));
        expect($response->headers->get('Location'))->not->toBeNull();
    });
});

describe('web resolve', function (): void {
    it('returns 302 redirect for web search', function (): void {
        GameVersion::factory()->create([
            'code' => '1.0.0-LIVE',
            'channel' => 'live',
            'is_default' => true,
            'released_at' => now(),
        ]);

        $manufacturer = Manufacturer::factory()->create();

        $item = Item::factory()->create(['slug' => 'web-redirect-item']);
        ItemData::factory()
            ->for($item)
            ->for(GameVersion::first(), 'gameVersion')
            ->for($manufacturer)
            ->create([
                'name' => 'WebRedirectItem',
                'class_name' => 'web_redirect_item',
                'classification' => 'Test',
                'data' => [],
            ]);

        $this->get('/search/WebRedirectItem')
            ->assertStatus(302)
            ->assertRedirect(route('web.items.show', ['item' => 'web-redirect-item']));
    });
});
