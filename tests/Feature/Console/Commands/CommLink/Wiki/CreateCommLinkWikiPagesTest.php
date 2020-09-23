<?php

declare(strict_types=1);

namespace Tests\Feature\Console\Commands\CommLink\Wiki;

use App\Jobs\Wiki\CommLink\CreateCommLinkWikiPages;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class CreateCommLinkWikiPagesTest extends TestCase
{
    /**
     * Test handle
     *
     * @return void
     */
    public function testHandle(): void
    {
        Bus::fake();

        $this->artisan('comm-links:create-wiki-pages')
            ->expectsOutput('Dispatching Comm-Link Wiki Page Creation')
            ->assertExitCode(0);

        Bus::assertDispatched(CreateCommLinkWikiPages::class);
    }
}
