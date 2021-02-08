<?php

namespace Tests\Feature\Jobs\Rsi\CommLink\Download;

use App\Jobs\Rsi\CommLink\Download\DownloadCommLink;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Class DownloadStatsJobTest
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class DownloadCommLinkTest extends TestCase
{
    /**
     * @covers \App\Jobs\Rsi\CommLink\Download\DownloadCommLink::handle
     *
     * @return void
     */
    public function testRequestWithServerError(): void
    {
        Http::fake(
            [
                '*' => Http::response('INVALID', 503, []),
            ]
        );

        $job = $this->partialMock(
            DownloadCommLink::class,
            function ($mock) {
                $mock->shouldReceive('fail')->once();
            }
        );

        Queue::fake();

        $job->handle();
    }

    /**
     * @covers \App\Jobs\Rsi\CommLink\Download\DownloadCommLink::handle
     * @covers \App\Jobs\Rsi\CommLink\Download\DownloadCommLink::removeRsiToken
     * @covers \App\Jobs\Rsi\CommLink\Download\DownloadCommLink::writeFile
     *
     * @return void
     */
    public function testRequestWithSuccess(): void
    {
        Http::fake(
            [
                '*' => Http::response('id="post"', 200, []),
            ]
        );

        Storage::persistentFake('comm_links');

        $job = new DownloadCommLink(12663);
        $job->handle();

        Storage::persistentFake('comm_links')->assertExists(12663);
    }
}
