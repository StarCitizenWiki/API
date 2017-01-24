<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class CrowdfundingStatsTest extends TestCase
{
    /** @var  \App\Repositories\StarCitizen\APIv1\Stats\StatsRepository */
    private $_api;

    public function setUp()
    {
        parent::setUp();
        $this->_api = $this->app->make('StarCitizen\StatsAPI');
    }

    public function testCrowdfundingStats()
    {
        $crowdfundingStats = $this->_api->getCrowdfundStats()->asResponse();
        $content = $crowdfundingStats->getBody()->getContents();

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
        $this->expectException(\App\Exceptions\EmptyResponseException::class);
        $this->_api->asJSON();
    }
}
