<?php
/**
 * User: Keonie
 * Date: 09.09.2018 17:36
 */

namespace Tests;



use App\Jobs\Api\StarCitizen\Starmap\DownloadStarmap;

class ExampleTest extends TestCase
{

    /** @test */
    public function testBasicExample() {
        $job = new DownloadStarmap(false);
        $job->handle();
    }
}