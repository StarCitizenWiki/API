<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Repositories\StarCitizen\APIv1\Stats\StatsRepository;

class CrowdfundingStatsTest extends TestCase
{
    public function testCrowdfundingStats()
    {
        $api = new StatsRepository();
        $crowdfundingStats = $api->getCrowdfundStats();
        $content = $crowdfundingStats->getBody()->getContents();

        $this->assertSame('application/json', $crowdfundingStats->getHeader('Content-Type')[0]);
        $this->assertNotEmpty($content);
        $this->assertContains('OK', $content);
    }

    public function testView()
    {
        $this->visit('/apiv1/crowdfunding')->see('success');
    }
}
