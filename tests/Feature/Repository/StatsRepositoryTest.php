<?php

namespace Tests\Feature\Repository;

use App\Exceptions\InvalidDataException;
use App\Repositories\StarCitizen\APIv1\StatsRepository;
use Tests\TestCase;

/**
 * Class StatsRepositoryTest
 * @package Tests\Feature\Repository
 * @covers \App\Repositories\BaseAPITrait
 * @covers \App\Repositories\StarCitizen\BaseStarCitizenAPI
 */
class StatsRepositoryTest extends TestCase
{

    /** @var  \App\Repositories\StarCitizen\APIv1\StatsRepository */
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
     * @covers \App\Repositories\StarCitizen\APIv1\StatsRepository::getAll()
     */
    public function testAllRepository()
    {
        $content = $this->repository->getAll()->asJSON();
        $this->assertNotEmpty($content);
        $this->assertContains('fans', $content);
    }

    /**
     * @covers \App\Repositories\StarCitizen\APIv1\StatsRepository::getFans()
     */
    public function testFansRepository()
    {
        $content = $this->repository->getFans()->asJSON();
        $this->assertNotEmpty($content);
        $this->assertContains('fans', $content);
    }

    /**
     * @covers \App\Repositories\StarCitizen\APIv1\StatsRepository::getFleet()
     */
    public function testFleetRepository()
    {
        $content = $this->repository->getFleet()->asJSON();
        $this->assertNotEmpty($content);
        $this->assertContains('fleet', $content);
    }

    /**
     * @covers \App\Repositories\StarCitizen\APIv1\StatsRepository::getFunds()
     */
    public function testFundsRepository()
    {
        $content = $this->repository->getFunds()->asJSON();
        $this->assertNotEmpty($content);
        $this->assertContains('funds', $content);
    }

    /**
     * Tests the MissingTransformerException
     * @covers \App\Exceptions\InvalidDataException
     */
    public function testEmptyResponseException()
    {
        $this->expectException(InvalidDataException::class);
        $this->repository->asJSON();
    }
}
