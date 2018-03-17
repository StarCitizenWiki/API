<?php declare(strict_types = 1);

namespace Tests\Feature\Repository;

use App\Repositories\StarCitizen\ApiV1\StatsRepository;
use Tests\TestCase;

/**
 * Class StatsRepositoryTest
 * @covers \App\Repositories\AbstractBaseRepository
 */
class StatsRepositoryTest extends TestCase
{

    /** @var  \App\Repositories\StarCitizen\ApiV1\StatsRepository */
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
     * @covers \App\Repositories\StarCitizen\ApiV1\StatsRepository::getAll()
     */
    public function testAllRepository()
    {
        $content = $this->repository->getAll()->toJson();
        $this->assertNotEmpty($content);
        $this->assertContains('fans', $content);
    }

    /**
     * @covers \App\Repositories\StarCitizen\ApiV1\StatsRepository::getFans()
     */
    public function testFansRepository()
    {
        $content = $this->repository->getFans()->toJson();
        $this->assertNotEmpty($content);
        $this->assertContains('fans', $content);
    }

    /**
     * @covers \App\Repositories\StarCitizen\ApiV1\StatsRepository::getFleet()
     */
    public function testFleetRepository()
    {
        $content = $this->repository->getFleet()->toJson();
        $this->assertNotEmpty($content);
        $this->assertContains('fleet', $content);
    }

    /**
     * @covers \App\Repositories\StarCitizen\ApiV1\StatsRepository::getFunds()
     */
    public function testFundsRepository()
    {
        $content = $this->repository->getFunds()->toJson();
        $this->assertNotEmpty($content);
        $this->assertContains('funds', $content);
    }
}
