<?php

declare(strict_types=1);

namespace Tests\Feature\Console\Commands\CommLink\Download;

use App\Jobs\Rsi\CommLink\Download\DownloadMissingCommLinks;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class DownloadCommLinksTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testHandle(): void
    {
        Bus::fake();

        $this->artisan('comm-links:download-all')
            ->expectsOutput('Downloading all Comm-Links')
            ->assertExitCode(0);

        Bus::assertDispatched(DownloadMissingCommLinks::class);
    }
}
