<?php

declare(strict_types=1);

namespace Tests\Feature\Console\Commands\CommLink;

use App\Jobs\Rsi\CommLink\Download\DownloadMissingCommLinks;
use App\Jobs\Rsi\CommLink\Image\CreateImageHashes;
use App\Jobs\Rsi\CommLink\Image\CreateImageMetadata;
use App\Jobs\Rsi\CommLink\Import\ImportCommLinks;
use App\Jobs\Rsi\CommLink\Translate\TranslateCommLinks;
use App\Jobs\Wiki\CommLink\CreateCommLinkWikiPages;
use App\Models\Rsi\CommLink\CommLink;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class CommLinksScheduleTest extends TestCase
{
    /**
     * Test basic chain
     *
     * @return void
     */
    public function testHandle(): void
    {
        Queue::fake();

        $this->artisan('comm-links:schedule')->assertExitCode(0);

        Queue::assertPushedWithChain(
            DownloadMissingCommLinks::class,
            [
                ImportCommLinks::class,
                CreateImageMetadata::class,
                CreateImageHashes::class,
            ]
        );
    }

    /**
     * Test DeepL chain
     *
     * @return void
     */
    public function testHandleDeepLChain(): void
    {
        app('config')->set('services.deepl.auth_key', 'invalid');

        Queue::fake();

        $this->artisan('comm-links:schedule')->assertExitCode(0);

        Queue::assertPushedWithChain(
            DownloadMissingCommLinks::class,
            [
                ImportCommLinks::class,
                CreateImageMetadata::class,
                CreateImageHashes::class,
                TranslateCommLinks::class,
            ]
        );
    }

    /**
     * Test create wiki pages chain
     *
     * @return void
     */
    public function testHandleWikiChain(): void
    {
        app('config')->set('services.mediawiki.client_id', 'invalid');
        app('config')->set('mediawiki.api_url', 'invalid');

        Queue::fake();

        $this->artisan('comm-links:schedule')->assertExitCode(0);

        Queue::assertPushedWithChain(
            DownloadMissingCommLinks::class,
            [
                ImportCommLinks::class,
                CreateImageMetadata::class,
                CreateImageHashes::class,
                CreateCommLinkWikiPages::class,
            ]
        );
    }

    /**
     * Test all chain
     *
     * @return void
     */
    public function testHandleAllChain(): void
    {
        app('config')->set('services.deepl.auth_key', 'invalid');
        app('config')->set('services.mediawiki.client_id', 'invalid');
        app('config')->set('mediawiki.api_url', 'invalid');

        Queue::fake();

        $this->artisan('comm-links:schedule')->assertExitCode(0);

        Queue::assertPushedWithChain(
            DownloadMissingCommLinks::class,
            [
                ImportCommLinks::class,
                CreateImageMetadata::class,
                CreateImageHashes::class,
                TranslateCommLinks::class,
                CreateCommLinkWikiPages::class,
            ]
        );
    }

    /**
     * Test handle with an existing comm link offset
     */
    public function testHandleExistingOffset(): void
    {
        CommLink::factory()->create(['id' => 12663]);

        Queue::fake();

        $this->artisan('comm-links:schedule')->assertExitCode(0);

        Queue::assertPushedWithChain(
            DownloadMissingCommLinks::class,
            [
                ImportCommLinks::class,
                CreateImageMetadata::class,
                CreateImageHashes::class,
            ]
        );
    }
}
