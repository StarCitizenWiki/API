<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class CrowdfundingStatsTest extends TestCase
{
    public function testJsonResponse() {
        $this->visit('/test')
             ->seeJson(['code' => 'OK']);
    }
}
