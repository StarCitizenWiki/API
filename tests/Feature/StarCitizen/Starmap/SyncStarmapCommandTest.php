<?php

declare(strict_types=1);

use App\Jobs\StarCitizen\Starmap\Sync\SyncStarmap;
use Illuminate\Support\Facades\Bus;

it('dispatches a sync job', function (): void {
    Bus::fake();

    $this->artisan('starmap:sync')
        ->assertExitCode(0);

    Bus::assertDispatched(SyncStarmap::class);
});
