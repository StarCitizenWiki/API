<?php

declare(strict_types=1);

use App\Jobs\Game\ImportItemPrices;
use App\Models\Game\GameVersion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

it('dispatches job for default game version', function (): void {
    GameVersion::factory()->create(['is_default' => false, 'code' => '4.1.0']);
    $defaultVersion = GameVersion::factory()->create(['is_default' => true, 'code' => '4.0.0']);
    Queue::fake();

    $this->artisan('game:import-item-prices')
        ->assertSuccessful()
        ->expectsOutputToContain("Dispatching item price import for version {$defaultVersion->code}")
        ->expectsOutputToContain('Job dispatched successfully.');

    Queue::assertPushedTimes(ImportItemPrices::class, 1);
});

it('fails when no default game version exists', function (): void {
    GameVersion::factory()->create(['is_default' => false]);
    Queue::fake();

    $this->artisan('game:import-item-prices')
        ->assertFailed()
        ->expectsOutput('No default game version found.');

    Queue::assertNothingPushed();
});
