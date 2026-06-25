<?php

declare(strict_types=1);

use App\Jobs\Game\ImportCommodityPrices as ImportCommodityPricesJob;
use App\Jobs\Game\ImportItemPrices as ImportItemPricesJob;
use App\Models\Game\GameVersion;
use Illuminate\Support\Facades\Bus;

it('dispatches import batch for default game version', function (): void {
    GameVersion::factory()->create(['is_default' => false, 'code' => '4.1.0']);
    $defaultVersion = GameVersion::factory()->create(['is_default' => true, 'code' => '4.0.0']);

    Bus::fake();

    $this->artisan('game:import-item-prices')
        ->assertSuccessful()
        ->expectsOutputToContain("Dispatching price import for version {$defaultVersion->code}")
        ->expectsOutputToContain('Import batch dispatched');

    Bus::assertBatchCount(1);

    Bus::assertBatched(function ($batch): bool {
        return $batch->jobs->count() === 2
            && $batch->jobs->first() instanceof ImportItemPricesJob
            && $batch->jobs->last() instanceof ImportCommodityPricesJob;
    });
});

it('fails when no default game version exists', function (): void {
    GameVersion::factory()->create(['is_default' => false]);

    Bus::fake();

    $this->artisan('game:import-item-prices')
        ->assertFailed()
        ->expectsOutput('No default game version found.');

    Bus::assertNothingBatched();
});
