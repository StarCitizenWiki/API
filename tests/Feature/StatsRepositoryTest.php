<?php

namespace Tests\Feature;

use App\Exceptions\InvalidDataException;
use App\Exceptions\MissingTransformerException;
use App\Http\Controllers\StarCitizen\StatsAPIController;
use App\Repositories\StarCitizen\APIv1\Stats\StatsRepository;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

class StatsRepositoryTest extends TestCase
{

    /** @var  \App\Repositories\StarCitizen\APIv1\Stats\StatsRepository */
    private $repository;

    /**
     * Resolve Repository
     */
    public function setUp()
    {
        parent::setUp();
        $this->repository = resolve(StatsRepository::class);
    }

    /**
     * Tests the retrieval of all stats from the repository
     *
     * @covers \App\Repositories\StarCitizen\APIv1\Stats\StatsRepository::getAll()
     */
    public function testAllRepository()
    {
        $content = $this->repository->getAll()->asJSON();
        $this->assertNotEmpty($content);
        $this->assertContains('OK', $content);
    }

    /**
     * @covers \App\Repositories\StarCitizen\APIv1\Stats\StatsRepository::getFans()
     */
    public function testFansRepository()
    {
        $content = $this->repository->getFans()->asJSON();
        $this->assertNotEmpty($content);
        $this->assertContains('fans', $content);
    }

    /**
     * @covers \App\Repositories\StarCitizen\APIv1\Stats\StatsRepository::getFleet()
     */
    public function testFleetRepository()
    {
        $content = $this->repository->getFleet()->asJSON();
        $this->assertNotEmpty($content);
        $this->assertContains('fleet', $content);
    }

    /**
     * @covers \App\Repositories\StarCitizen\APIv1\Stats\StatsRepository::getFunds()
     */
    public function testFundsRepository()
    {
        $content = $this->repository->getFunds()->asJSON();
        $this->assertNotEmpty($content);
        $this->assertContains('funds', $content);
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
