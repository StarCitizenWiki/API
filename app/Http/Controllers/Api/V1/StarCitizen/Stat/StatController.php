<?php declare(strict_types = 1);

namespace App\Http\Controllers\Api\V1\StarCitizen\Stat;

use App\Http\Controllers\Controller;
use App\Repositories\Api\V1\StarCitizen\Interfaces\Stats\StatsRepositoryInterface;

/**
 * @Resource("Stats", uri="/stats")
 */
class StatController extends Controller
{
    /**
     * StatsRepository
     *
     * @var \App\Repositories\Api\V1\StarCitizen\Stats\StatsRepository
     */
    private $repository;

    /**
     * StatsAPIController constructor.
     *
     * @param \App\Repositories\Api\V1\StarCitizen\Interfaces\Stats\StatsRepositoryInterface $repository
     */
    public function __construct(StatsRepositoryInterface $repository)
    {
        parent::__construct();
        $this->repository = $repository;
    }

    /**
     * Get Latest Crowdfund Stats
     *
     * @Get("/latest")
     *
     * @Versions({"v1"})
     *
     * @return \Dingo\Api\Http\Response
     */
    public function getLatest()
    {
        return $this->repository->getLatest();
    }

    /**
     * Get All Crowdfund Stats paginated
     *
     * @Get("/all")
     *
     * @Versions({"v1"})
     *
     * @Parameters({
     *      @Parameter("page", description="The page of results to view.", default=1)
     * })
     *
     * @return \Dingo\Api\Http\Response
     */
    public function getAll()
    {
        return $this->repository->getAll();
    }
}
