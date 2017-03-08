<?php

namespace Tests\Feature;

use App\Exceptions\MissingTransformerException;
use Tests\TestCase;

class CrowdfundingStatsTest extends TestCase
{
    /** @var  \App\Repositories\StarCitizen\APIv1\Stats\StatsRepository */
    private $_statsAPI;

    public function setUp()
    {
        parent::setUp();
        $this->_statsAPI = $this->app->make('StarCitizen\API\StatsRepository');
    }

    /**
     * Tests the retrieval of all stats
     */
    public function testCrowdfundingStats()
    {
        $content = $this->_statsAPI->getAll()->getResponse();
        $this->assertNotEmpty($content);
        $this->assertContains('OK', $content->toJson());
    }

    /**
     * tests the api view
     */
    public function testView()
    {
        $this->getJson('/api/v1/stats/all')->assertStatus(200);
    }

    /**
     * Tests the MissingTransformerException
     */
    public function testEmptyResponseException()
    {
        $this->expectException(MissingTransformerException::class);
        $this->_statsAPI->asJSON();
    }
}
