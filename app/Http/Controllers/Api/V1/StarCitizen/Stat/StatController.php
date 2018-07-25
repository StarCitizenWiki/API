<?php declare(strict_types = 1);

namespace App\Http\Controllers\Api\V1\StarCitizen\Stat;

use App\Http\Controllers\Controller;
use App\Repositories\Api\V1\StarCitizen\Interfaces\Stat\StatRepositoryInterface;

/**
 * @Resource("Stats", uri="/stats")
 */
class StatController extends Controller
{
    /**
     * StatsRepository
     *
     * @var \App\Repositories\Api\V1\StarCitizen\Stat\StatRepository
     */
    private $repository;

    /**
     * StatsAPIController constructor.
     *
     * @param \App\Repositories\Api\V1\StarCitizen\Interfaces\Stat\StatRepositoryInterface $repository
     */
    public function __construct(StatRepositoryInterface $repository)
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
     * @Response(200, body={
     *     "data": {
     *          "funds": "180000000",
     *          "fans": "2000000",
     *          "fleet": "1500000",
     *          "timestamp": "2018-01-02 20:00:00"
     *      }
     * })
     *
     * @return \Dingo\Api\Http\Response
     */
    public function latest()
    {
        return $this->repository->latest();
    }

    /**
     * Get All Crowdfund Stats paginated
     *
     * @Get("/")
     *
     * @Versions({"v1"})
     *
     * @Parameters({
     *      @Parameter("page", description="The page of results to view.", default=1)
     * })
     *
     * @Response(200, body={
     *     "data": {
     *          {
     *              "funds": "180000000",
     *              "fans": "2000000",
     *              "fleet": "1500000",
     *              "timestamp": "2018-01-02 20:00:00"
     *          },
     *          {
     *              "funds": "...",
     *              "fans": "...",
     *              "fleet": "...",
     *              "timestamp": "2018-01-01 20:00:00"
     *          }
     *     },
     *     "meta": {
     *          "pagination": {
     *              "total": 1000,
     *              "count": 15,
     *              "per_page": 15,
     *              "current_page": 1,
     *              "total_pages": 100,
     *              "links": {
     *                  "next": "Link",
     *                  "prev": "Link"
     *              }
     *          }
     *     }
     * })
     *
     * @return \Dingo\Api\Http\Response
     */
    public function index()
    {
        return $this->repository->all();
    }
}
