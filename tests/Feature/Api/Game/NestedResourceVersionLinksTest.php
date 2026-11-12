<?php

declare(strict_types=1);

use App\Http\Resources\Game\Item\ItemLinkResource;
use App\Http\Resources\Game\Starmap\StarmapLocationLinkResource;
use App\Http\Resources\Game\Vehicle\HardpointItemResource;
use App\Http\Resources\Game\Vehicle\TurretSummaryResource;
use App\Models\Game\Commodity\Commodity;
use App\Models\Game\GameVersion;
use App\Models\Game\Item;
use App\Models\Game\ItemData;
use App\Models\Game\StarmapLocationData;
use Illuminate\Http\Request;

use function Tests\Support\attachLocation;
use function Tests\Support\createResourceData;

beforeEach(function (): void {
    $this->defaultVersion = GameVersion::factory()->create(['code' => '4.0.0-LIVE', 'is_default' => true]);
    $this->versionRequest = Request::create('/api/test?version=4.0.0-LIVE');
});

it('keeps the version on commodity location links', function (string $endpoint, string $path): void {
    $commodity = Commodity::factory()->create();
    $resource = createResourceData($commodity);
    attachLocation($resource, 'Stanton', 'Planet', 'Hurston');
    $url = $endpoint === 'index' ? '/api/commodities' : '/api/commodities/'.$commodity->uuid;
    $link = $this->getJson($url.'?version=4.0.0-LIVE')->assertOk()->json($path);
    expect($link)->toBeString()->toContain('version=4.0.0-LIVE');
})->with([
    'CommodityIndexResource' => ['index', 'data.0.locations.0.link'],
    'CommodityShowResource' => ['show', 'data.locations.0.link'],
]);

it('keeps the version on turret weapon links', function (): void {
    $payload = (new TurretSummaryResource(['Weapons' => [['UUID' => 'weapon-uuid']]]))->resolve($this->versionRequest);
    foreach (['link', 'web_url'] as $key) {
        expect($payload['weapons'][0][$key])->toContain('version=4.0.0-LIVE');
    }
});

it('keeps the version on hardpoint item links', function (): void {
    $item = Item::factory()->create();
    ItemData::factory()->for($item)->for($this->defaultVersion, 'gameVersion')->create(['data' => ['stdItem' => []]]);
    $item->load('data.gameVersion', 'data.manufacturer');
    $payload = (new HardpointItemResource($item))->resolve($this->versionRequest);
    expect($payload['link'])->toContain('version=4.0.0-LIVE');
});

it('keeps the version on item and base variant links', function (): void {
    $base = ItemData::factory()->for(Item::factory())->for($this->defaultVersion, 'gameVersion')->create();
    $variant = ItemData::factory()->for(Item::factory())->for($this->defaultVersion, 'gameVersion')->create(['base_id' => $base->id]);
    $variant->load('item', 'baseVariant.item');
    $payload = (new ItemLinkResource($variant))->resolve($this->versionRequest);
    foreach (['link', 'web_url', 'base_variant'] as $key) {
        expect($payload[$key])->toContain('version=4.0.0-LIVE');
    }
});

it('keeps the version on starmap location links', function (): void {
    $location = new StarmapLocationData(['location_uuid' => 'location-uuid']);
    $payload = StarmapLocationLinkResource::toLinkArray($location, 'location-uuid', $this->versionRequest);
    foreach (['link', 'web_url'] as $key) {
        expect($payload[$key])->toContain('version=4.0.0-LIVE');
    }
});
