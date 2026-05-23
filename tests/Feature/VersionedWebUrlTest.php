<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\Game\Item;
use App\Models\Game\ItemData;
use App\Models\Game\Manufacturer;
use App\Models\Game\Vehicle;
use App\Models\Game\VehicleData;

describe('PersistSelectedGameVersion middleware', function () {
    it('injects session version into request query when no version query param is present', function (): void {
        $defaultVersion = GameVersion::factory()->create([
            'code' => '4.7.0-LIVE.1',
            'is_default' => true,
        ]);

        $ptuVersion = GameVersion::factory()->create([
            'code' => '4.8.0-PTU.1',
            'is_default' => false,
        ]);

        // First request: set the session via query param
        $this->get(route('home', ['version' => $ptuVersion->code]));
        $this->assertSame($ptuVersion->code, session('game_version_code'));

        // Second request: no version in URL, session should inject it into query
        $response = $this->get(route('home'));
        $response->assertSuccessful();

        // The version dropdown should still show the PTU version as selected
        $response->assertSee($ptuVersion->code);
    });

    it('does not inject version when the session version is the default', function (): void {
        $defaultVersion = GameVersion::factory()->create([
            'code' => '4.7.0-LIVE.1',
            'is_default' => true,
        ]);

        // Request with default version
        $this->get(route('home', ['version' => $defaultVersion->code]));
        $this->assertNull(session('game_version_code'));
    });
});

describe('web_url includes version in API responses', function () {
    it('includes version in item show web_url', function (): void {
        GameVersion::factory()->create([
            'code' => '4.7.0-LIVE.1',
            'is_default' => true,
        ]);

        $ptuVersion = GameVersion::factory()->create([
            'code' => '4.8.0-PTU.1',
            'is_default' => false,
        ]);

        $manufacturer = Manufacturer::factory()->create();
        $item = Item::factory()->create(['slug' => 'test-item']);
        ItemData::factory()
            ->for($item)
            ->for($ptuVersion, 'gameVersion')
            ->for($manufacturer)
            ->create([
                'name' => 'Test Item',
                'class_name' => 'TestItem',
                'type' => 'Widget',
                'classification' => 'Test.Widget',
            ]);

        $response = $this->getJson(route('items.show', ['identifier' => $item->uuid, 'version' => $ptuVersion->code]));
        $response->assertSuccessful();
        $response->assertJsonPath('data.web_url', fn (string $url): bool => str_contains($url, 'version='.$ptuVersion->code));
    });

    it('includes version in vehicle show web_url', function (): void {
        GameVersion::factory()->create([
            'code' => '4.7.0-LIVE.1',
            'is_default' => true,
        ]);

        $ptuVersion = GameVersion::factory()->create([
            'code' => '4.8.0-PTU.1',
            'is_default' => false,
        ]);

        $vehicle = Vehicle::factory()->create(['slug' => 'test-vehicle']);
        VehicleData::factory()
            ->for($vehicle)
            ->for($ptuVersion, 'gameVersion')
            ->create(['display_name' => 'Test Vehicle']);

        $response = $this->getJson(route('vehicles.show', ['vehicle' => $vehicle->uuid, 'version' => $ptuVersion->code]));
        $response->assertSuccessful();
        $response->assertJsonPath('data.web_url', fn (string $url): bool => str_contains($url, 'version='.$ptuVersion->code));
    });

    it('omits version from web_url when using default version', function (): void {
        $defaultVersion = GameVersion::factory()->create([
            'code' => '4.7.0-LIVE.1',
            'is_default' => true,
        ]);

        $manufacturer = Manufacturer::factory()->create();
        $item = Item::factory()->create(['slug' => 'default-item']);
        ItemData::factory()
            ->for($item)
            ->for($defaultVersion, 'gameVersion')
            ->for($manufacturer)
            ->create([
                'name' => 'Default Item',
                'class_name' => 'DefaultItem',
                'type' => 'Widget',
                'classification' => 'Test.Widget',
            ]);

        $response = $this->getJson(route('items.show', ['identifier' => $item->uuid]));
        $response->assertSuccessful();
        $response->assertJsonPath('data.web_url', fn (string $url): bool => ! str_contains($url, 'version='));
    });
});
