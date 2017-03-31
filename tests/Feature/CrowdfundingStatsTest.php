<?php

namespace Tests\Feature;

use App\Exceptions\InvalidDataException;
use App\Exceptions\MissingTransformerException;
use App\Repositories\StarCitizen\APIv1\Stats\StatsRepository;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

class CrowdfundingStatsTest extends TestCase
{

    /** @var  \App\Repositories\StarCitizen\APIv1\Stats\StatsRepository */
    private $repository;

    public function setUp()
    {
        parent::setUp();
        $this->repository = resolve(StatsRepository::class);
    }

    /**
     * Tests the retrieval of all stats from the repository
     */
    public function testCrowdfundingStats()
    {
        $content = $this->repository->getAll()->asJSON();
        $this->assertNotEmpty($content);
        $this->assertContains('OK', $content);
    }

    /**
     * Tests Stats from API
     */
    public function testCrowdfundingApiView()
    {
        $response = $this->get('api/v1/stats/all');
        $response->assertStatus(200);
        $response->assertSee('data');
    }

    /**
     * Tests fans API
     */
    public function testFansApiView()
    {
        $response = $this->get('api/v1/stats/fans');
        $response->assertStatus(200);
        $response->assertSee('fans');
    }

    /**
     * Tests Funds API
     */
    public function testFundsApiView()
    {
        $response = $this->get('api/v1/stats/funds');
        $response->assertStatus(200);
        $response->assertSee('funds');
    }

    /**
     * Tests fleet api
     */
    public function testFleetApiView()
    {
        $response = $this->get('api/v1/stats/fleet');
        $response->assertStatus(200);
        $response->assertSee('fleet');
    }

    /**
     * Tests the MissingTransformerException
     */
    public function testEmptyResponseException()
    {
        $this->expectException(InvalidDataException::class);
        $this->repository->asJSON();
    }
}
