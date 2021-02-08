<?php

declare(strict_types=1);

namespace Tests\Feature\Console\Commands\CommLink\Download;

use App\Jobs\Rsi\CommLink\Download\ReDownloadDbCommLinks;
use App\Jobs\Rsi\CommLink\Import\ImportCommLinks;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ReDownloadCommLinksTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testHandle(): void
    {
        Queue::fake();

        $this->artisan('comm-links:download-new-versions -s')
            ->assertExitCode(0);

        Queue::assertPushedWithChain(
            ReDownloadDbCommLinks::class,
            [
                ImportCommLinks::class,
            ]
        );
    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testHandleMissingSkipOption(): void
    {
        Queue::fake();

        $this->artisan('comm-links:download-new-versions')
            ->assertExitCode(0);

        Queue::assertPushedWithChain(
            ReDownloadDbCommLinks::class,
            [
                ImportCommLinks::class,
            ]
        );
    }
}
