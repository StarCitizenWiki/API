<?php

declare(strict_types=1);

use App\Console\Commands\Game\ImportItemPrices;
use App\Jobs\Game\EnrichItemPrices as EnrichItemPricesJob;
use App\Jobs\Game\EnrichVehiclePrices as EnrichVehiclePricesJob;
use App\Models\Game\GameVersion;
use App\Models\Game\Item;
use App\Models\Game\ItemData;
use App\Models\Game\Vehicle;
use App\Models\Game\VehicleData;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;

beforeEach(function (): void {
    $this->version = GameVersion::factory()->create(['is_default' => true]);
});

it('dispatches item enrichment batch for items with prices', function (): void {
    $item = Item::factory()->create();
    ItemData::factory()->create([
        'item_id' => $item->id,
        'game_version_id' => $this->version->id,
        'uex_prices' => [['terminal_code' => 'T1', 'terminal_name' => 'T', 'price_buy' => 100, 'price_sell' => 50, 'date_updated' => '2024-01-01T00:00:00+00:00']],
    ]);

    Bus::fake();
    Http::fake(['api.uexcorp.uk/*' => Http::response(['data' => []])]);

    ImportItemPrices::dispatchEnrichmentBatches($this->version, 50, null);

    Bus::assertBatchCount(1);

    Bus::assertBatched(function ($batch): bool {
        return $batch->jobs->count() === 1
            && $batch->jobs->first() instanceof EnrichItemPricesJob;
    });
});

it('dispatches vehicle enrichment batch for vehicles with prices', function (): void {
    $vehicle = Vehicle::factory()->create();
    VehicleData::factory()->create([
        'vehicle_id' => $vehicle->id,
        'game_version_id' => $this->version->id,
        'uex_purchase_prices' => [['terminal_code' => 'T1', 'terminal_name' => 'T', 'price_buy' => 1000000, 'date_updated' => '2024-01-01T00:00:00+00:00']],
    ]);

    Bus::fake();
    Http::fake(['api.uexcorp.uk/*' => Http::response(['data' => []])]);

    ImportItemPrices::dispatchEnrichmentBatches($this->version, 50, null);

    Bus::assertBatchCount(1);

    Bus::assertBatched(function ($batch): bool {
        return $batch->jobs->count() === 1
            && $batch->jobs->first() instanceof EnrichVehiclePricesJob;
    });
});

it('chunks item enrichment jobs correctly', function (): void {
    $items = Item::factory()->count(5)->create();
    foreach ($items as $item) {
        ItemData::factory()->create([
            'item_id' => $item->id,
            'game_version_id' => $this->version->id,
            'uex_prices' => [['terminal_code' => 'T', 'terminal_name' => 'T', 'price_buy' => 100, 'price_sell' => 50, 'date_updated' => '2024-01-01T00:00:00+00:00']],
        ]);
    }

    Bus::fake();
    Http::fake(['api.uexcorp.uk/*' => Http::response(['data' => []])]);

    ImportItemPrices::dispatchEnrichmentBatches($this->version, 2, null);

    Bus::assertBatchCount(1);

    Bus::assertBatched(fn ($batch): bool => $batch->jobs->count() === 3);
});

it('chunks vehicle enrichment jobs correctly', function (): void {
    $vehicles = Vehicle::factory()->count(5)->create();
    foreach ($vehicles as $vehicle) {
        VehicleData::factory()->create([
            'vehicle_id' => $vehicle->id,
            'game_version_id' => $this->version->id,
            'uex_purchase_prices' => [['terminal_code' => 'T', 'terminal_name' => 'T', 'price_buy' => 100, 'date_updated' => '2024-01-01T00:00:00+00:00']],
        ]);
    }

    Bus::fake();
    Http::fake(['api.uexcorp.uk/*' => Http::response(['data' => []])]);

    ImportItemPrices::dispatchEnrichmentBatches($this->version, 2, null);

    Bus::assertBatchCount(1);

    Bus::assertBatched(fn ($batch): bool => $batch->jobs->count() === 3);
});

it('dispatches both item and vehicle enrichment batches', function (): void {
    $item = Item::factory()->create();
    ItemData::factory()->create([
        'item_id' => $item->id,
        'game_version_id' => $this->version->id,
        'uex_prices' => [['terminal_code' => 'T', 'terminal_name' => 'T', 'price_buy' => 100, 'price_sell' => 50, 'date_updated' => '2024-01-01T00:00:00+00:00']],
    ]);

    $vehicle = Vehicle::factory()->create();
    VehicleData::factory()->create([
        'vehicle_id' => $vehicle->id,
        'game_version_id' => $this->version->id,
        'uex_purchase_prices' => [['terminal_code' => 'T', 'terminal_name' => 'T', 'price_buy' => 1000000, 'date_updated' => '2024-01-01T00:00:00+00:00']],
    ]);

    Bus::fake();
    Http::fake(['api.uexcorp.uk/*' => Http::response(['data' => []])]);

    ImportItemPrices::dispatchEnrichmentBatches($this->version, 50, null);

    Bus::assertBatchCount(2);
});

it('skips enrichment when no prices exist', function (): void {
    Bus::fake();

    ImportItemPrices::dispatchEnrichmentBatches($this->version, 50, null);

    Bus::assertNothingBatched();
});
