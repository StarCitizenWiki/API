<?php

declare(strict_types=1);

use App\Jobs\StarCitizen\Galactapedia\Sync\SyncGalactapedia;
use Illuminate\Support\Facades\Bus;

it('dispatches a galactapedia sync job', function (): void {
    Bus::fake();

    $this->artisan('galactapedia:sync')
        ->assertExitCode(0);

    Bus::assertDispatched(SyncGalactapedia::class);
});
