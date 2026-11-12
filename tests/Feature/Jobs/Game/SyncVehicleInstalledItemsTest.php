<?php

declare(strict_types=1);

use App\Jobs\Game\ImportVehicleData;
use App\Jobs\Game\SyncVehicleInstalledItems;
use App\Models\Game\GameVersion;
use App\Models\Game\Item;
use App\Models\Game\ItemData;
use App\Models\Game\Manufacturer;
use App\Models\Game\VehicleData;
use Illuminate\Support\Facades\Storage;

it('repairs an early vehicle import after its items arrive without touching other versions', function (): void {
    Storage::fake('scunpacked');
    $version = GameVersion::factory()->create();
    $other = GameVersion::factory()->create();
    $manufacturer = Manufacturer::factory()->create();
    $item = Item::factory()->create();
    $payload = ['UUID' => fake()->uuid(), 'ClassName' => 'TEST', 'Name' => 'Test', 'Manufacturer' => ['UUID' => $manufacturer->uuid], 'Loadout' => [['Loadout' => [['UUID' => $item->uuid]]]]];
    Storage::disk('scunpacked')->put('ships/test.json', json_encode($payload, JSON_THROW_ON_ERROR));
    (new ImportVehicleData($version->id, 'ships/test.json'))->handle();
    $vehicle = VehicleData::where('game_version_id', $version->id)->firstOrFail();
    expect($vehicle->installedItems()->count())->toBe(0);
    $otherItem = ItemData::factory()->create(['item_id' => $item->id, 'game_version_id' => $other->id]);
    $itemData = ItemData::factory()->create(['item_id' => $item->id, 'game_version_id' => $version->id]);
    (new SyncVehicleInstalledItems($version->id))->handle();
    (new SyncVehicleInstalledItems($version->id))->handle();
    expect($vehicle->installedItems()->pluck('game_item_data.id')->all())->toBe([$itemData->id])
        ->and($vehicle->installedItems()->where('game_item_data.id', $otherItem->id)->exists())->toBeFalse();
});

it('keeps immediate standalone sync and clears stale pivots on reimport', function (): void {
    Storage::fake('scunpacked');
    $version = GameVersion::factory()->create();
    $manufacturer = Manufacturer::factory()->create();
    $item = Item::factory()->create();
    $itemData = ItemData::factory()->create(['item_id' => $item->id, 'game_version_id' => $version->id]);
    $payload = ['UUID' => fake()->uuid(), 'ClassName' => 'TEST', 'Name' => 'Test', 'Manufacturer' => ['UUID' => $manufacturer->uuid], 'Loadout' => [['UUID' => $item->uuid]]];
    Storage::disk('scunpacked')->put('ships/test.json', json_encode($payload, JSON_THROW_ON_ERROR));
    (new ImportVehicleData($version->id, 'ships/test.json'))->handle();
    $vehicle = VehicleData::where('game_version_id', $version->id)->firstOrFail();
    expect($vehicle->installedItems()->pluck('game_item_data.id')->all())->toBe([$itemData->id]);
    $payload['Loadout'] = [];
    Storage::disk('scunpacked')->put('ships/test.json', json_encode($payload, JSON_THROW_ON_ERROR));
    (new ImportVehicleData($version->id, 'ships/test.json'))->handle();
    expect($vehicle->installedItems()->count())->toBe(0);
});

it('does not restore an old loadout from a stale vehicle model', function (): void {
    Storage::fake('scunpacked');
    $version = GameVersion::factory()->create();
    $manufacturer = Manufacturer::factory()->create();
    $item = Item::factory()->create();
    ItemData::factory()->create(['item_id' => $item->id, 'game_version_id' => $version->id]);
    $payload = ['UUID' => fake()->uuid(), 'ClassName' => 'TEST', 'Name' => 'Test', 'Manufacturer' => ['UUID' => $manufacturer->uuid], 'Loadout' => [['UUID' => $item->uuid]]];
    Storage::disk('scunpacked')->put('ships/test.json', json_encode($payload, JSON_THROW_ON_ERROR));
    (new ImportVehicleData($version->id, 'ships/test.json'))->handle();
    $stale = VehicleData::where('game_version_id', $version->id)->firstOrFail();
    $payload['Loadout'] = [];
    Storage::disk('scunpacked')->put('ships/test.json', json_encode($payload, JSON_THROW_ON_ERROR));
    (new ImportVehicleData($version->id, 'ships/test.json'))->handle();
    SyncVehicleInstalledItems::syncVehicle($stale);
    expect($stale->installedItems()->count())->toBe(0);
});
