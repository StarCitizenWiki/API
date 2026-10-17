<?php

declare(strict_types=1);

use App\Jobs\StarCitizen\Galactapedia\Sync\SyncGalactapedia;
use App\Jobs\StarCitizen\Starmap\Sync\SyncStarmap;
use App\Jobs\StarCitizen\Stat\SyncStats;
use Illuminate\Support\Facades\Bus;

describe('galactapedia:sync', function (): void {
    it('dispatches a galactapedia sync job', function (): void {
        Bus::fake();

        $this->artisan('galactapedia:sync')
            ->expectsOutput('Dispatching Galactapedia Sync')
            ->assertExitCode(0);

        Bus::assertDispatched(SyncGalactapedia::class);
    });
});

describe('starmap:sync', function (): void {
    it('dispatches a sync job', function (): void {
        Bus::fake();

        $this->artisan('starmap:sync')
            ->expectsOutput('Dispatching Starmap Sync')
            ->assertExitCode(0);

        Bus::assertDispatched(SyncStarmap::class);
    });
});

describe('stats:sync', function (): void {
    it('dispatches a stats sync job', function (): void {
        Bus::fake();

        $this->artisan('stats:sync')
            ->expectsOutput('Dispatching Stats Sync')
            ->assertExitCode(0);

        Bus::assertDispatchedTimes(SyncStats::class, 1);
    });
});
