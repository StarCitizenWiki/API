<?php

namespace Tests\Feature\Jobs\Api\StarCitizen\Stat;

use App\Jobs\Api\StarCitizen\Stat\DownloadStats;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Class DownloadStatsJobTest
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class DownloadStatsJobTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @covers \App\Jobs\Api\StarCitizen\Stat\DownloadStats::handle
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

        Storage::fake('stats')->delete(now()->year);

        $job = $this->partialMock(
            DownloadStats::class,
            function ($mock) {
                $mock->shouldReceive('fail')->once();
            }
        );

        Queue::fake();

        $job->handle();
    }

    /**
     * A basic feature test example.
     *
     * @covers \App\Jobs\Api\StarCitizen\Stat\DownloadStats::handle
     * @covers \App\Jobs\Api\StarCitizen\Stat\DownloadStats::parseResponseBody
     *
     * @return void
     */
    public function testRequestWithInvalidData(): void
    {
        Http::fake(
            [
                '*' => Http::response('INVALID', 200, []),
            ]
        );

        Storage::fake('stats');
        Storage::fake('stats')->delete(now()->year);

        $job = $this->partialMock(
            DownloadStats::class,
            function ($mock) {
                $mock->shouldReceive('fail')->once();
            }
        );

        Queue::fake();

        $job->handle();
    }

    /**
     * A basic feature test example.
     *
     * @covers \App\Jobs\Api\StarCitizen\Stat\DownloadStats::handle
     * @covers \App\Jobs\Api\StarCitizen\Stat\DownloadStats::saveStats
     * @covers \App\Jobs\Api\StarCitizen\Stat\DownloadStats::parseResponseBody
     *
     * @return void
     */
    public function testRequestWithSuccess(): void
    {
        Http::fake(
            [
                '*' => Http::response('{"success":1, "data": []}', 200, []),
            ]
        );

        Storage::fake('stats');

        $job = new DownloadStats();

        $job->handle();

        Storage::disk('stats')->assertExists(
            sprintf('%d/stats_%s.json', now()->year, now()->format('Y-m-d'))
        );

        Storage::fake('stats')->delete(now()->year);
    }
}
