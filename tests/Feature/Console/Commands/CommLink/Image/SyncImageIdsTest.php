<?php

declare(strict_types=1);

namespace Tests\Feature\Console\Commands\CommLink\Image;

use App\Jobs\Rsi\CommLink\SyncImageIds;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class SyncImageIdsTest extends TestCase
{
    /**
     * Test handle without offset
     *
     * @return void
     */
    public function testHandle(): void
    {
        Bus::fake();

        $this->artisan('comm-links:sync-image-ids')
            ->expectsOutput('Dispatching Comm-Link Image Sync')
            ->expectsOutput('Starting at Comm-Link ID 12663')
            ->assertExitCode(0);

        Bus::assertDispatched(SyncImageIds::class);
    }

    /**
     * Test handle with offset
     *
     * @return void
     */
    public function testHandleOffset(): void
    {
        Bus::fake();

        $this->artisan('comm-links:sync-image-ids 10')
            ->expectsOutput('Dispatching Comm-Link Image Sync')
            ->expectsOutput('Starting at Comm-Link ID 12673')
            ->assertExitCode(0);

        Bus::assertDispatched(SyncImageIds::class);
    }

    /**
     * Test handle with invalid offset
     *
     * @return void
     */
    public function testHandleInvalidOffset(): void
    {
        Bus::fake();

        $this->artisan('comm-links:sync-image-ids asdf')
            ->expectsOutput('Dispatching Comm-Link Image Sync')
            ->expectsOutput('Starting at Comm-Link ID 12663')
            ->assertExitCode(0);

        Bus::assertDispatched(SyncImageIds::class);
    }
}
