<?php

declare(strict_types=1);

use App\Models\Game\Blueprint;
use App\Models\Game\BlueprintData;
use App\Models\Game\GameVersion;
use App\Models\Game\GameVersionAlias;
use App\Models\Game\Item;
use App\Models\Game\ItemData;
use App\Models\Game\Manufacturer;
use App\Models\Game\Mission\Mission;
use App\Models\Game\Mission\MissionData;
use App\Models\Game\Vehicle;
use App\Models\Game\VehicleData;
use Illuminate\Support\Str;

function createNotFoundVersions(): array
{
    $oldest = GameVersion::factory()->create([
        'code' => '3.19.0-LIVE',
        'channel' => 'live',
        'is_default' => false,
        'released_at' => now()->subDays(2),
    ]);

    $older = GameVersion::factory()->create([
        'code' => '3.20.0-LIVE',
        'channel' => 'live',
        'is_default' => false,
        'released_at' => now()->subDay(),
    ]);

    $default = GameVersion::factory()->create([
        'code' => '3.21.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    return ['oldest' => $oldest, 'older' => $older, 'default' => $default];
}

it('lists available versions when an item only exists in other versions', function (): void {
    $versions = createNotFoundVersions();
    $manufacturer = Manufacturer::factory()->create();

    $item = Item::factory()->create();

    ItemData::factory()->for($item)->for($versions['oldest'], 'gameVersion')->for($manufacturer)->create([
        'name' => 'Oldest Payload',
        'class_name' => 'oldest_payload',
        'type' => 'Widget',
        'classification' => 'Test.Widget',
        'data' => ['stdItem' => []],
    ]);

    ItemData::factory()->for($item)->for($versions['older'], 'gameVersion')->for($manufacturer)->create([
        'name' => 'Older Payload',
        'class_name' => 'older_payload',
        'type' => 'Widget',
        'classification' => 'Test.Widget',
        'data' => ['stdItem' => []],
    ]);

    $this->getJson("/api/items/{$item->uuid}")
        ->assertStatus(404)
        ->assertJsonPath('requested_version', '3.21.0-LIVE')
        ->assertJsonPath('available_versions', ['3.20.0-LIVE', '3.19.0-LIVE'])
        ->assertJsonPath('message', 'No Item data found for game version "3.21.0-LIVE".');
});

it('lists available versions when an item is requested by slug for the wrong version', function (): void {
    $versions = createNotFoundVersions();
    $manufacturer = Manufacturer::factory()->create();

    $item = Item::factory()->create(['slug' => 'versioned-widget']);
    ItemData::factory()->for($item)->for($versions['older'], 'gameVersion')->for($manufacturer)->create([
        'name' => 'Older Payload',
        'class_name' => 'older_payload',
        'type' => 'Widget',
        'classification' => 'Test.Widget',
        'data' => ['stdItem' => []],
    ]);

    $this->getJson('/api/items/versioned-widget')
        ->assertStatus(404)
        ->assertJsonPath('available_versions', ['3.20.0-LIVE']);
});

it('reports the requested version when an explicit version misses', function (): void {
    $versions = createNotFoundVersions();
    $manufacturer = Manufacturer::factory()->create();

    $item = Item::factory()->create();
    ItemData::factory()->for($item)->for($versions['default'], 'gameVersion')->for($manufacturer)->create([
        'name' => 'Default Payload',
        'class_name' => 'default_payload',
        'type' => 'Widget',
        'classification' => 'Test.Widget',
        'data' => ['stdItem' => []],
    ]);

    $this->getJson("/api/items/{$item->uuid}?version=3.19.0-LIVE")
        ->assertStatus(404)
        ->assertJsonPath('requested_version', '3.19.0-LIVE')
        ->assertJsonPath('available_versions', ['3.21.0-LIVE']);
});

it('reports the raw alias as requested version when an alias resolves to a version without data', function (): void {
    $versions = createNotFoundVersions();
    $manufacturer = Manufacturer::factory()->create();

    GameVersionAlias::create([
        'code' => 'LIVE',
        'game_version_id' => $versions['default']->id,
    ]);

    $item = Item::factory()->create();
    ItemData::factory()->for($item)->for($versions['older'], 'gameVersion')->for($manufacturer)->create([
        'name' => 'Older Payload',
        'class_name' => 'older_payload',
        'type' => 'Widget',
        'classification' => 'Test.Widget',
        'data' => ['stdItem' => []],
    ]);

    $this->getJson("/api/items/{$item->uuid}?version=live")
        ->assertStatus(404)
        ->assertJsonPath('requested_version', 'live')
        ->assertJsonPath('available_versions', ['3.20.0-LIVE'])
        ->assertJsonPath('message', 'No Item data found for game version "3.21.0-LIVE".');
});

it('returns a plain 404 for an item that never existed', function (): void {
    createNotFoundVersions();

    $this->getJson('/api/items/'.Str::uuid()->toString())
        ->assertStatus(404)
        ->assertJsonMissingPath('available_versions');

    $this->getJson('/api/items/never-existed-anywhere')
        ->assertStatus(404)
        ->assertJsonMissingPath('available_versions');
});

it('lists available versions when an item only in other versions is requested by class name', function (): void {
    $versions = createNotFoundVersions();
    $manufacturer = Manufacturer::factory()->create();

    $item = Item::factory()->create();
    ItemData::factory()->for($item)->for($versions['older'], 'gameVersion')->for($manufacturer)->create([
        'name' => 'Older Payload',
        'class_name' => 'older_payload',
        'type' => 'Widget',
        'classification' => 'Test.Widget',
        'data' => ['stdItem' => []],
    ]);

    $this->getJson('/api/items/older_payload')
        ->assertStatus(404)
        ->assertJsonPath('requested_version', '3.21.0-LIVE')
        ->assertJsonPath('available_versions', ['3.20.0-LIVE']);
});

it('lists available versions when a vehicle only exists in other versions', function (): void {
    $versions = createNotFoundVersions();
    $manufacturer = Manufacturer::factory()->create();

    $vehicle = Vehicle::factory()->create();
    VehicleData::factory()->for($vehicle)->for($versions['older'], 'gameVersion')->for($manufacturer)->create();

    $this->getJson("/api/vehicles/{$vehicle->uuid}")
        ->assertStatus(404)
        ->assertJsonPath('requested_version', '3.21.0-LIVE')
        ->assertJsonPath('available_versions', ['3.20.0-LIVE'])
        ->assertJsonPath('message', 'No Vehicle data found for game version "3.21.0-LIVE".');
});

it('lists available versions when a vehicle is requested by slug for the wrong version', function (): void {
    $versions = createNotFoundVersions();
    $manufacturer = Manufacturer::factory()->create();

    $vehicle = Vehicle::factory()->create(['slug' => 'versioned-hull']);
    VehicleData::factory()->for($vehicle)->for($versions['older'], 'gameVersion')->for($manufacturer)->create();

    $this->getJson('/api/vehicles/versioned-hull')
        ->assertStatus(404)
        ->assertJsonPath('available_versions', ['3.20.0-LIVE']);
});

it('lists available versions when a vehicle only in other versions is requested by class name', function (): void {
    $versions = createNotFoundVersions();
    $manufacturer = Manufacturer::factory()->create();

    $vehicle = Vehicle::factory()->create();
    VehicleData::factory()->for($vehicle)->for($versions['older'], 'gameVersion')->for($manufacturer)->create([
        'class_name' => 'AEGS_Arrow_Versioned',
        'name' => 'Arrow Versioned',
    ]);

    $this->getJson('/api/vehicles/AEGS_Arrow_Versioned')
        ->assertStatus(404)
        ->assertJsonPath('requested_version', '3.21.0-LIVE')
        ->assertJsonPath('available_versions', ['3.20.0-LIVE']);
});

it('lists available versions when a vehicle only in other versions is requested by name', function (): void {
    $versions = createNotFoundVersions();
    $manufacturer = Manufacturer::factory()->create();

    $vehicle = Vehicle::factory()->create();
    VehicleData::factory()->for($vehicle)->for($versions['older'], 'gameVersion')->for($manufacturer)->create([
        'class_name' => 'AEGS_Arrow_Versioned',
        'name' => 'Arrow Versioned',
    ]);

    $this->getJson('/api/vehicles/'.rawurlencode('Arrow Versioned'))
        ->assertStatus(404)
        ->assertJsonPath('requested_version', '3.21.0-LIVE')
        ->assertJsonPath('available_versions', ['3.20.0-LIVE']);
});

it('returns a plain 404 for a vehicle that never existed', function (): void {
    createNotFoundVersions();

    $this->getJson('/api/vehicles/'.Str::uuid()->toString())
        ->assertStatus(404);

    $this->getJson('/api/vehicles/never-existed-anywhere')
        ->assertStatus(404)
        ->assertJsonMissingPath('available_versions');
});

it('lists available versions when a mission only exists in other versions', function (): void {
    $versions = createNotFoundVersions();

    $mission = Mission::factory()->create();
    MissionData::factory()->for($mission)->for($versions['older'], 'gameVersion')->create();

    $this->getJson("/api/missions/{$mission->uuid}")
        ->assertStatus(404)
        ->assertJsonPath('requested_version', '3.21.0-LIVE')
        ->assertJsonPath('available_versions', ['3.20.0-LIVE'])
        ->assertJsonPath('message', 'No Mission data found for game version "3.21.0-LIVE".');
});

it('returns a plain 404 for a mission that never existed', function (): void {
    createNotFoundVersions();

    $this->getJson('/api/missions/'.Str::uuid()->toString())
        ->assertStatus(404);
});

it('lists available versions when a blueprint has no data for the requested version', function (): void {
    $versions = createNotFoundVersions();

    $blueprint = Blueprint::factory()->create();
    BlueprintData::factory()->for($blueprint)->for($versions['older'], 'gameVersion')->create();

    $this->getJson("/api/blueprints/{$blueprint->uuid}")
        ->assertStatus(404)
        ->assertJsonPath('requested_version', '3.21.0-LIVE')
        ->assertJsonPath('available_versions', ['3.20.0-LIVE'])
        ->assertJsonPath('message', 'No Blueprint data found for game version "3.21.0-LIVE".');
});

it('returns a plain 404 for a blueprint that never existed', function (): void {
    createNotFoundVersions();

    $this->getJson('/api/blueprints/'.Str::uuid()->toString())
        ->assertStatus(404);
});

it('lists an undated upcoming version first among available versions', function (): void {
    $versions = createNotFoundVersions();
    $upcoming = GameVersion::factory()->create([
        'code' => '3.22.0-EXP',
        'channel' => 'exp',
        'is_default' => false,
        'released_at' => null,
    ]);
    $manufacturer = Manufacturer::factory()->create();

    $item = Item::factory()->create();
    ItemData::factory()->for($item)->for($versions['older'], 'gameVersion')->for($manufacturer)->create([
        'name' => 'Older Payload',
        'class_name' => 'older_payload',
        'type' => 'Widget',
        'classification' => 'Test.Widget',
        'data' => ['stdItem' => []],
    ]);
    ItemData::factory()->for($item)->for($upcoming, 'gameVersion')->for($manufacturer)->create([
        'name' => 'Upcoming Payload',
        'class_name' => 'upcoming_payload',
        'type' => 'Widget',
        'classification' => 'Test.Widget',
        'data' => ['stdItem' => []],
    ]);

    $this->getJson("/api/items/{$item->uuid}")
        ->assertStatus(404)
        ->assertJsonPath('requested_version', '3.21.0-LIVE')
        ->assertJsonPath('available_versions', ['3.22.0-EXP', '3.20.0-LIVE']);
});

it('excludes hidden versions from available versions', function (): void {
    $versions = createNotFoundVersions();
    $hidden = GameVersion::factory()->create([
        'code' => '3.20.5-LIVE',
        'channel' => 'live',
        'is_default' => false,
        'is_hidden' => true,
        'released_at' => now()->subHours(12),
    ]);
    $manufacturer = Manufacturer::factory()->create();

    $item = Item::factory()->create();
    ItemData::factory()->for($item)->for($hidden, 'gameVersion')->for($manufacturer)->create([
        'name' => 'Hidden Payload',
        'class_name' => 'hidden_payload',
        'type' => 'Widget',
        'classification' => 'Test.Widget',
        'data' => ['stdItem' => []],
    ]);
    ItemData::factory()->for($item)->for($versions['older'], 'gameVersion')->for($manufacturer)->create([
        'name' => 'Older Payload',
        'class_name' => 'older_payload',
        'type' => 'Widget',
        'classification' => 'Test.Widget',
        'data' => ['stdItem' => []],
    ]);

    $this->getJson("/api/items/{$item->uuid}")
        ->assertStatus(404)
        ->assertJsonPath('requested_version', '3.21.0-LIVE')
        ->assertJsonPath('available_versions', ['3.20.0-LIVE']);
});
