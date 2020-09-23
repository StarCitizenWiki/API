<?php

declare(strict_types=1);

namespace Tests\Feature\Console\Commands\CommLink\Download\Image;

use App\Jobs\Rsi\CommLink\Download\Image\DownloadCommLinkImages;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class DownloadCommLinkImagesTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testHandle(): void
    {
        Bus::fake();

        $this->artisan('comm-links:download-images')
            ->assertExitCode(0);

        Bus::assertDispatched(DownloadCommLinkImages::class);
    }
}
