<?php declare(strict_types = 1);

namespace App\Http\Controllers\Api\V1\StarCitizen\Stat;

use App\Http\Controllers\Api\AbstractApiController as ApiController;
use App\Models\Api\StarCitizen\Stat;
use App\Transformers\Api\V1\StarCitizen\Stat\StatTransformer;

/**
 * @Resource("Stats", uri="/stats")
 */
class StatController extends ApiController
{
    /**
     * StatsRepository
     *
     * @var \App\Transformers\Api\V1\StarCitizen\Stat\StatTransformer
     */
    private $transformer;

    /**
     * StatsAPIController constructor.
     *
     * @param \App\Transformers\Api\V1\StarCitizen\Stat\StatTransformer $transformer
     */
    public function __construct(StatTransformer $transformer)
    {
        $this->transformer = $transformer;
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
        $stat = Stat::orderByDesc('created_at')->first();

        if (null === $stat) {
            return $this->emptyStat();
        }

        return $this->response->item($stat, $this->transformer);
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
        $stats = Stat::orderByDesc('created_at')->paginate();

        if (null === $stats) {
            return $this->emptyStat();
        }

        return $this->response->paginator($stats, $this->transformer);
    }

    /**
     * Empty Model
     *
     * @return \Dingo\Api\Http\Response
     */
    private function emptyStat()
    {
        return $this->response->item(new Stat(), $this->transformer);
    }
}
