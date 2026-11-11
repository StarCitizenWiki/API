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

    it('resolves a vehicle-item by its item name when the vehicle has data in the version', function (): void {
        $uuid = 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee';

        $vehicle = Vehicle::factory()->create([
            'slug' => 'linked-vehicle',
            'uuid' => $uuid,
        ]);
        VehicleData::factory()
            ->for($vehicle)
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Linked Vehicle',
                'display_name' => 'Linked Vehicle',
                'class_name' => 'AEGS_Linked_Vehicle',
            ]);

        $item = Item::factory()->create([
            'slug' => 'linked-vehicle-item',
            'uuid' => $uuid,
        ]);
        ItemData::factory()
            ->for($item)
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Completely Distinct Item Name',
                'class_name' => 'AEGS_Distinct_Item_Name',
                'classification' => 'Vehicle',
                'data' => [],
            ]);

        $this->getJson('/api/search/Completely%20Distinct%20Item%20Name')
            ->assertStatus(302)
            ->assertRedirect(route('items.show', ['identifier' => 'linked-vehicle-item']));
    });

    it('does not resolve a vehicle-item whose vehicle lacks data in the version', function (): void {
        $uuid = 'ffffffff-eeee-dddd-cccc-bbbbbbbbbbbb';

        Vehicle::factory()->create([
            'slug' => 'unreleased-vehicle',
            'uuid' => $uuid,
        ]);

        $item = Item::factory()->create([
            'slug' => 'unreleased-vehicle-item',
            'uuid' => $uuid,
        ]);
        ItemData::factory()
            ->for($item)
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Unreleased Vehicle Item',
                'class_name' => 'AEGS_Unreleased_Vehicle_Item',
                'classification' => 'Vehicle',
                'data' => [],
            ]);

        $this->getJson('/api/search/Unreleased%20Vehicle%20Item')
            ->assertStatus(404);
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

    it('preserves duplicate scalar and array page parameters on the redirect', function (): void {
        $item = Item::factory()->create(['slug' => 'duplicate-page-item']);
        ItemData::factory()
            ->for($item)
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Duplicate Page Item',
                'class_name' => 'duplicate_page_item',
                'classification' => 'Test',
                'data' => [],
            ]);

        $response = $this->getJson("/api/search/{$item->uuid}?page=2&page[size]=50");

        $response->assertStatus(302);

        expect((string) $response->headers->get('Location'))
            ->toEndWith('?page=2&page[size]=50');
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

    it('does not redirect when the uuid has no data in the default version', function (): void {
        $v2 = GameVersion::factory()->create([
            'code' => '2.0.0-LIVE',
            'channel' => 'live',
            'is_default' => false,
            'released_at' => now()->addDay(),
        ]);

        $item = Item::factory()->create(['slug' => 'version-miss-item']);
        ItemData::factory()
            ->for($item)
            ->for($v2, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Version Miss Item',
                'class_name' => 'version_miss_item',
                'classification' => 'Test',
                'data' => [],
            ]);

        $this->getJson("/api/search/{$item->uuid}")
            ->assertStatus(404)
            ->assertJson([
                'message' => 'No Item data found for game version "1.0.0-LIVE".',
                'requested_version' => '1.0.0-LIVE',
                'available_versions' => ['2.0.0-LIVE'],
            ]);

        $this->getJson("/api/search/{$item->uuid}?version=2.0.0-LIVE")
            ->assertStatus(302)
            ->assertRedirect(route('items.show', ['identifier' => 'version-miss-item']).'?version=2.0.0-LIVE');
    });

    it('returns plain 404 when the uuid has no data in any version', function (): void {
        $item = Item::factory()->create();

        $this->getJson("/api/search/{$item->uuid}")
            ->assertStatus(404)
            ->assertJson(['message' => 'No matching entity found.']);
    });

    it('returns a version 404 instead of redirecting to a vehicle-item without vehicle data', function (): void {
        $v2 = GameVersion::factory()->create([
            'code' => '2.0.0-LIVE',
            'channel' => 'live',
            'is_default' => false,
            'released_at' => now()->addDay(),
        ]);

        $vehicle = Vehicle::factory()->create(['slug' => 'version-miss-vehicle']);
        VehicleData::factory()
            ->for($vehicle)
            ->for($v2, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Version Miss Vehicle',
                'display_name' => 'Version Miss Vehicle',
                'class_name' => 'ANVL_VersionMissVehicle',
            ]);

        $item = Item::factory()->create([
            'slug' => 'version-miss-vehicle-item',
            'uuid' => $vehicle->uuid,
        ]);
        ItemData::factory()
            ->for($item)
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Version Miss Vehicle Item',
                'class_name' => 'ANVL_VersionMissVehicle_Item',
                'classification' => 'Test',
                'data' => [],
            ]);

        $this->getJson("/api/search/{$vehicle->uuid}")
            ->assertStatus(404)
            ->assertJson([
                'message' => 'No Vehicle data found for game version "1.0.0-LIVE".',
                'requested_version' => '1.0.0-LIVE',
                'available_versions' => ['2.0.0-LIVE'],
            ]);
    });

    it('redirects to the vehicle when vehicle data exists in the requested version', function (): void {
        $vehicle = Vehicle::factory()->create(['slug' => 'same-uuid-vehicle']);
        VehicleData::factory()
            ->for($vehicle)
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Same Uuid Vehicle',
                'display_name' => 'Same Uuid Vehicle',
                'class_name' => 'ANVL_SameUuidVehicle',
            ]);

        $item = Item::factory()->create([
            'slug' => 'same-uuid-vehicle-item',
            'uuid' => $vehicle->uuid,
        ]);
        ItemData::factory()
            ->for($item)
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Same Uuid Vehicle Item',
                'class_name' => 'ANVL_SameUuidVehicle_Item',
                'classification' => 'Test',
                'data' => [],
            ]);

        $this->getJson("/api/search/{$vehicle->uuid}")
            ->assertStatus(302)
            ->assertRedirect(route('vehicles.show', ['vehicle' => 'same-uuid-vehicle']));
    });

    it('does not resolve a vehicle-item by name when the vehicle lacks data in the requested version', function (): void {
        $v2 = GameVersion::factory()->create([
            'code' => '2.0.0-LIVE',
            'channel' => 'live',
            'is_default' => false,
            'released_at' => now()->addDay(),
        ]);

        $vehicle = Vehicle::factory()->create(['slug' => 'text-miss-vehicle']);
        VehicleData::factory()
            ->for($vehicle)
            ->for($v2, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Text Miss Ship',
                'display_name' => 'Text Miss Ship',
                'class_name' => 'ANVL_TextMissShip',
            ]);

        $item = Item::factory()->create([
            'slug' => 'text-miss-vehicle-item',
            'uuid' => $vehicle->uuid,
        ]);
        ItemData::factory()
            ->for($item)
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Text Miss Ship',
                'class_name' => 'ANVL_TextMissShip',
                'classification' => 'Test',
                'data' => [],
            ]);

        $this->getJson('/api/search/'.rawurlencode('Text Miss Ship'))
            ->assertStatus(404)
            ->assertJson(['message' => 'No matching entity found.']);
    });

    it('does not redirect systemless locations even with data in the default version', function (): void {
        $location = StarmapLocation::factory()->create();
        StarmapLocationData::factory()->create([
            'starmap_location_id' => $location->id,
            'game_version_id' => $this->gameVersion->id,
            'system' => null,
        ]);

        $this->getJson("/api/search/{$location->uuid}")
            ->assertStatus(404)
            ->assertJson(['message' => 'No matching entity found.']);
    });

    it('lists available versions for a location without data in the default version', function (): void {
        $v2 = GameVersion::factory()->create([
            'code' => '2.0.0-LIVE',
            'channel' => 'live',
            'is_default' => false,
            'released_at' => now()->addDay(),
        ]);

        $location = StarmapLocation::factory()->create();
        StarmapLocationData::factory()->create([
            'starmap_location_id' => $location->id,
            'game_version_id' => $v2->id,
            'system' => 'Stanton',
        ]);

        $this->getJson("/api/search/{$location->uuid}")
            ->assertStatus(404)
            ->assertJson([
                'message' => 'No Location data found for game version "1.0.0-LIVE".',
                'requested_version' => '1.0.0-LIVE',
                'available_versions' => ['2.0.0-LIVE'],
            ]);
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

    it('resolves a vehicle by partial name via LIKE fallback', function (): void {
        $vehicle = Vehicle::factory()->create(['slug' => 'origin-300i']);
        VehicleData::factory()
            ->for($vehicle)
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Origin 300i',
                'class_name' => 'ORIG_300i',
            ]);

        $this->getJson('/api/search/300i')
            ->assertStatus(302)
            ->assertRedirect(route('vehicles.show', ['vehicle' => 'origin-300i']));
    });

    it('resolves a vehicle by partial class name via LIKE fallback', function (): void {
        $vehicle = Vehicle::factory()->create(['slug' => 'origin-300i']);
        VehicleData::factory()
            ->for($vehicle)
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Origin 300i',
                'class_name' => 'ORIG_300i',
            ]);

        $this->getJson('/api/search/ORIG_300')
            ->assertStatus(302)
            ->assertRedirect(route('vehicles.show', ['vehicle' => 'origin-300i']));
    });

    it('prefers exact match over LIKE match for vehicles', function (): void {
        $vehicle = Vehicle::factory()->create(['slug' => 'origin-300i']);
        VehicleData::factory()
            ->for($vehicle)
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Origin 300i',
                'class_name' => 'ORIG_300i',
            ]);

        $vehicle2 = Vehicle::factory()->create(['slug' => 'origin-315p']);
        VehicleData::factory()
            ->for($vehicle2)
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Origin 315p',
                'class_name' => 'ORIG_315p',
            ]);

        $this->getJson('/api/search/Origin 300i')
            ->assertStatus(302)
            ->assertRedirect(route('vehicles.show', ['vehicle' => 'origin-300i']));
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
