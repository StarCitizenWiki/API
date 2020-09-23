<?php

declare(strict_types=1);

namespace Tests\Feature\Console\Commands\CommLink\Translate;

use App\Jobs\Rsi\CommLink\Translate\TranslateCommLinks;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class TranslateCommLinksTest extends TestCase
{
    /**
     * Test handle without offset
     *
     * @return void
     */
    public function testHandle(): void
    {
        Bus::fake();

        $this->artisan('comm-links:translate')
            ->expectsOutput('Dispatching Comm-Link Translation')
            ->expectsOutput('Starting at Comm-Link ID 12663')
            ->assertExitCode(0);

        Bus::assertDispatched(TranslateCommLinks::class);
    }

    /**
     * Test handle with offset
     *
     * @return void
     */
    public function testHandleOffset(): void
    {
        Bus::fake();

        $this->artisan('comm-links:translate 10')
            ->expectsOutput('Dispatching Comm-Link Translation')
            ->expectsOutput('Starting at Comm-Link ID 12673')
            ->assertExitCode(0);

        Bus::assertDispatched(TranslateCommLinks::class);
    }
}
