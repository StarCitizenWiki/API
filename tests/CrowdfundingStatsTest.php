<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class CrowdfundingStatsTest extends TestCase
{
    /** @var  \App\Repositories\StarCitizen\APIv1\Stats\StatsRepository */
    private $_statsAPI;

    public function setUp()
    {
        parent::setUp();
        $this->_statsAPI = $this->app->make('StarCitizen\StatsAPI');
    }

    public function testCrowdfundingStats()
    {
        $crowdfundingStats = $this->_statsAPI->getCrowdfundStats()->asResponse();
        $content = (string) $crowdfundingStats->getBody();

        $this->assertSame('application/json', $crowdfundingStats->getHeader('Content-Type')[0]);
        $this->assertNotEmpty($content);
        $this->assertContains('OK', $content);
    }

    public function testView()
    {
        $this->visit('/api/v1/crowdfunding')->see('success');
    }

    public function testEmptyResponseException()
    {
        $this->expectException(\App\Exceptions\ResponseNotRequestedException::class);
        $this->_statsAPI->asJSON();
    }
}
