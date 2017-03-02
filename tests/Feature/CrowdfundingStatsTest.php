<?php

namespace Tests\Feature;

use App\Exceptions\InvalidDataException;
use Tests\TestCase;
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
        $this->_statsAPI = $this->app->make('StarCitizen\StatsRepository');
    }

    public function testCrowdfundingStats()
    {
        $content = $this->_statsAPI->getCrowdfundStats()->getResponse();
        $this->assertNotEmpty($content);
        $this->assertContains('OK', $content->toJson());
    }

    public function testView()
    {
        $this->getJson('/api/v1/stats/funds')->assertStatus(200);
    }

    public function testEmptyResponseException()
    {
        $this->expectException(InvalidDataException::class);
        $this->_statsAPI->asJSON();
    }
}
