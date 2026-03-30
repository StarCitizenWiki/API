<?php

declare(strict_types=1);

use App\Jobs\Game\ImportItemPrices;
use App\Models\Game\GameVersion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;

uses(RefreshDatabase::class);

it('dispatches job for default game version', function (): void {
    $gameVersion = GameVersion::factory()->create(['is_default' => true, 'code' => '4.0.0']);
    Bus::fake();

    $this->artisan('game:import-item-prices')
        ->assertSuccessful()
        ->expectsOutputToContain('Dispatching item price import for version 4.0.0')
        ->expectsOutputToContain('Job dispatched successfully.');

    Bus::assertDispatchedTimes(ImportItemPrices::class, 1);
    Bus::assertDispatched(ImportItemPrices::class, function (ImportItemPrices $job) use ($gameVersion): bool {
        $reflection = new ReflectionProperty($job, 'gameVersionId');
        $reflection->setAccessible(true);

        return $reflection->getValue($job) === $gameVersion->id;
    });
});

it('fails when no default game version exists', function (): void {
    GameVersion::factory()->create(['is_default' => false]);

    $this->artisan('game:import-item-prices')
        ->assertFailed()
        ->expectsOutput('No default game version found.');
});
