<?php

declare(strict_types=1);

use App\Jobs\StarCitizen\Stat\SyncStats;
use Illuminate\Support\Facades\Bus;

it('dispatches a stats sync job', function (): void {
    Bus::fake();

    $this->artisan('stats:sync')
        ->expectsOutput('Dispatching Stats Sync')
        ->assertExitCode(0);

    Bus::assertDispatchedTimes(SyncStats::class, 1);
});
