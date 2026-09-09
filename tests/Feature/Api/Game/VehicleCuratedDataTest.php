<?php

declare(strict_types=1);

use App\Http\Resources\Game\Vehicle\VehicleResource;
use App\Models\Game\GameVersion;
use App\Models\Game\Manufacturer;
use App\Models\Game\Vehicle;
use App\Models\Game\VehicleData;
use App\Models\StarCitizen\PledgeStore\PledgeStoreSku;
use App\Models\StarCitizen\ShipMatrix\Manufacturer as ShipMatrixManufacturer;
use App\Models\StarCitizen\ShipMatrix\ProductionNote;
use App\Models\StarCitizen\ShipMatrix\ProductionStatus;
use App\Models\StarCitizen\ShipMatrix\Vehicle\Size as ShipSize;
use App\Models\StarCitizen\ShipMatrix\Vehicle\Type as ShipType;
use App\Models\StarCitizen\ShipMatrix\Vehicle\Vehicle as ShipMatrixVehicle;

function createVehicleWithMatrixData(
    GameVersion $gameVersion,
    Manufacturer $gameManufacturer,
    ?ShipMatrixVehicle $shipMatrixVehicle = null,
    string $uuid = 'ce937681-caa2-4cfa-ab80-f5bf2a5b9a6c',
): VehicleData {
    $vehicle = Vehicle::query()->create(['uuid' => $uuid]);

    return VehicleData::query()->create([
        'vehicle_id' => $vehicle->id,
        'game_version_id' => $gameVersion->id,
        'manufacturer_id' => $gameManufacturer->id,
        'shipmatrix_id' => $shipMatrixVehicle?->id,
        'name' => 'Origin 300i',
        'class_name' => 'ORIG_300i',
        'data' => ['test' => 'data'],
    ]);
}

beforeEach(function () {
    $this->gameVersion = GameVersion::query()->create([
        'code' => '4.0.0-LIVE.24680357',
        'channel' => 'live',
        'is_default' => true,
    ]);

    $this->gameManufacturer = Manufacturer::query()->create([
        'uuid' => fake()->uuid(),
        'name' => 'Origin Jumpworks',
        'code' => 'ORIG',
    ]);

    $this->shipMatrixManufacturer = ShipMatrixManufacturer::query()->create([
        'cig_id' => 1,
        'name' => 'Origin Jumpworks',
        'name_short' => 'ORIG',
    ]);

    $this->shipMatrixVehicle = ShipMatrixVehicle::query()->create([
        'cig_id' => 12345,
        'chassis_id' => 100,
        'name' => '300i',
        'slug' => '300i',
        'manufacturer_id' => $this->shipMatrixManufacturer->id,
        'production_status_id' => ProductionStatus::query()->create(['slug' => 'flight-ready'])->id,
        'production_note_id' => ProductionNote::query()->create(['translation' => ['en' => 'Test Note']])->id,
        'type_id' => ShipType::query()->create(['slug' => 'multi-role'])->id,
        'size_id' => ShipSize::query()->create(['slug' => 'small'])->id,
        'msrp' => 60,
        'pledge_url' => '/pledge/ships/origin-300/300i',
    ]);
});

it('always includes a null curated_data key when no curated row exists', function (): void {
    $vehicleData = createVehicleWithMatrixData($this->gameVersion, $this->gameManufacturer, $this->shipMatrixVehicle);

    $this->getJson("/api/vehicles/{$vehicleData->vehicle->uuid}")
        ->assertOk()
        ->assertJsonPath('data.curated_data', null);
});

it('includes a null curated_data key on the vehicle index route', function (): void {
    createVehicleWithMatrixData($this->gameVersion, $this->gameManufacturer);

    $this->getJson('/api/vehicles')
        ->assertOk()
        ->assertJsonPath('data.0.curated_data', null);
});

it('renders null curated_data when the relation is not loaded', function (): void {
    $vehicleData = createVehicleWithMatrixData($this->gameVersion, $this->gameManufacturer);
    $vehicleData->vehicle->curatedData()->create([
        'wiki_page_title' => 'Origin 300i',
    ]);
    $vehicleData->load(['vehicle', 'gameVersion', 'manufacturer']);

    $payload = (new VehicleResource($vehicleData))->resolve(request());

    expect($payload)->toHaveKey('curated_data')
        ->and($payload['curated_data'])->toBeNull();
});

it('renders the populated curated_data block', function (): void {
    $vehicleData = createVehicleWithMatrixData($this->gameVersion, $this->gameManufacturer, $this->shipMatrixVehicle);

    $vehicleData->vehicle->curatedData()->create([
        'wiki_page_title' => 'Origin 300i',
        'synced_at' => '2026-09-09 05:00:00',
        'trailer_url' => 'https://www.youtube.com/watch?v=qDQ8dbE8qSo',
        'original_pledge_price' => 55,
        'original_warbond_price' => 55,
        'added_in_version' => 'Patch V0.8',
        'concept_date' => '2013-06-21',
        'sale_date' => '2013-06-21',
        'retire_date' => null,
        'pledge_availability' => 'Always available',
        'qa_urls' => ['https://robertsspaceindustries.com/spectrum/community/SC/forum/50272/thread/origin-300-series-qa'],
        'brochure_url' => 'https://robertsspaceindustries.com/media/0iplijfsgmlb8r/source/300series_brochure-1.pdf',
        'presentation_urls' => ['https://robertsspaceindustries.com/comm-link/transmission/17046-Origin-Celebration'],
        'whitleys_guide_url' => 'https://robertsspaceindustries.com/comm-link/spectrum-dispatch/19171-Whitleys-Guide-300-Series',
        'galactapedia_url' => 'https://robertsspaceindustries.com/galactapedia/article/0j46jjYgOn-origin-300i',
    ]);

    $response = $this->getJson("/api/vehicles/{$vehicleData->vehicle->uuid}");

    $response->assertOk();

    $curated = $response->json('data.curated_data');

    expect($curated)->toMatchArray([
        'source' => 'starcitizen.tools',
        'page_url' => 'https://starcitizen.tools/Origin_300i',
        'trailer_url' => 'https://www.youtube.com/watch?v=qDQ8dbE8qSo',
        'original_pledge_price' => 55,
        'original_warbond_price' => 55,
        'added_in_version' => 'Patch V0.8',
        'concept_date' => '2013-06-21',
        'sale_date' => '2013-06-21',
        'retire_date' => null,
        'pledge_availability' => 'Always available',
        'qa_urls' => ['https://robertsspaceindustries.com/spectrum/community/SC/forum/50272/thread/origin-300-series-qa'],
        'brochure_url' => 'https://robertsspaceindustries.com/media/0iplijfsgmlb8r/source/300series_brochure-1.pdf',
        'presentation_urls' => ['https://robertsspaceindustries.com/comm-link/transmission/17046-Origin-Celebration'],
        'whitleys_guide_url' => 'https://robertsspaceindustries.com/comm-link/spectrum-dispatch/19171-Whitleys-Guide-300-Series',
        'galactapedia_url' => 'https://robertsspaceindustries.com/galactapedia/article/0j46jjYgOn-origin-300i',
    ])
        ->and($curated)->toHaveKey('synced_at')
        ->and($curated['synced_at'])->toStartWith('2026-09-09T05:00:00');
});

it('includes warbond_price next to msrp when a warbond sku is on sale', function (): void {
    $vehicleData = createVehicleWithMatrixData($this->gameVersion, $this->gameManufacturer, $this->shipMatrixVehicle);

    // native_price is cents: 40000 -> $400, matching the live Railen-style warbond price.
    $pledgeSku = PledgeStoreSku::factory()->create([
        'name' => '300i Warbond',
        'native_price' => 40000,
        'is_warbond' => true,
        'stock_available' => true,
        'product_id' => 72,
    ]);
    $pledgeSku->ships()->sync([$this->shipMatrixVehicle->id]);

    $this->getJson("/api/vehicles/{$vehicleData->vehicle->uuid}")
        ->assertOk()
        ->assertJsonPath('data.msrp', 60)
        ->assertJsonPath('data.warbond_price', 400);
});

it('includes a null warbond_price key when no skus exist', function (): void {
    $vehicleData = createVehicleWithMatrixData($this->gameVersion, $this->gameManufacturer, $this->shipMatrixVehicle);

    $this->getJson("/api/vehicles/{$vehicleData->vehicle->uuid}")
        ->assertOk()
        ->assertJsonPath('data.warbond_price', null);
});

it('ignores skus that are not standalone warbond offers', function (array $skuOverrides): void {
    $vehicleData = createVehicleWithMatrixData($this->gameVersion, $this->gameManufacturer, $this->shipMatrixVehicle);

    $pledgeSku = PledgeStoreSku::factory()->create([
        'native_price' => 36000,
        ...$skuOverrides,
    ]);
    $pledgeSku->ships()->sync([$this->shipMatrixVehicle->id]);

    $this->getJson("/api/vehicles/{$vehicleData->vehicle->uuid}")
        ->assertOk()
        ->assertJsonPath('data.warbond_price', null);
})->with([
    'not a warbond' => [['is_warbond' => false]],
    'out of stock' => [['stock_available' => false]],
    'not a standalone ship' => [['product_id' => 268]],
]);

it('includes a null warbond_price next to a null msrp when no ship-matrix vehicle is linked', function (): void {
    $vehicleData = createVehicleWithMatrixData($this->gameVersion, $this->gameManufacturer);

    $this->getJson("/api/vehicles/{$vehicleData->vehicle->uuid}")
        ->assertOk()
        ->assertJsonPath('data.msrp', null)
        ->assertJsonPath('data.warbond_price', null);
});
