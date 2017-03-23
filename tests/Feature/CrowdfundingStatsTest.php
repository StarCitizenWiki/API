<?php

namespace Tests\Feature;

use App\Exceptions\InvalidDataException;
use App\Exceptions\MissingTransformerException;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

class CrowdfundingStatsTest extends TestCase
{
    use WithoutMiddleware;

    /** @var  \App\Repositories\StarCitizen\APIv1\Stats\StatsRepository */
    private $_statsAPI;

    public function setUp()
    {
        parent::setUp();
        config(['app.api_url' => 'localhost']);
        config(['app.tools_url' => 'null']);
        config(['app.shorturl_url' => 'null']);
        $this->_statsAPI = $this->app->make('StarCitizen\API\StatsRepository');
    }

    /**
     * Tests the retrieval of all stats
     */
    public function testCrowdfundingStats()
    {
        $content = $this->_statsAPI->getAll()->asJSON();
        $this->assertNotEmpty($content);
        $this->assertContains('OK', $content);
    }

    /**
     * Tests the MissingTransformerException
     */
    public function testEmptyResponseException()
    {
        $this->expectException(InvalidDataException::class);
        $this->_statsAPI->asJSON();
    }
}
