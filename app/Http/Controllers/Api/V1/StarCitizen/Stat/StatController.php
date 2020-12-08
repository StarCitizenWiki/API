<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\StarCitizen\Stat;

use App\Http\Controllers\Api\AbstractApiController as ApiController;
use App\Models\Api\StarCitizen\Stat\Stat;
use App\Transformers\Api\V1\StarCitizen\Stat\StatTransformer;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;

/**
 * Stat API
 * Returns current funding, fan and fleet stats
 * Import happens daily at 8PM UTC+1
 *
 * @Resource("Stats", uri="/stats")
 */
class StatController extends ApiController
{
    /**
     * StatsAPIController constructor.
     *
     * @param Request         $request
     * @param StatTransformer $transformer
     */
    public function __construct(Request $request, StatTransformer $transformer)
    {
        $this->transformer = $transformer;
        parent::__construct($request);
    }

    /**
     * Returns latest stats
     *
     * @Get("/latest")
     * @Versions({"v1"})
     * @Request(headers={"Accept": "application/x.StarCitizenWikiApi.v1+json"})
     * @Response(200, body={
     * "data": {
     *  "funds": "335901302.36",
     *  "fans": 2910921,
     *  "fleet": 2910921,
     *  "timestamp": "2020-12-06T19:00:55.000000Z"
     * },
     * "meta": {
     *  "processed_at": "2020-12-07 13:11:56"
     * }
     * })
     *
     * @return Response
     */
    public function latest(): Response
    {
        $stat = Stat::query()->orderByDesc('created_at')->first();

        return $this->getResponse($stat);
    }

    /**
     * Returns all funding stats
     *
     * @Get("/{?page,limit}")
     * @Versions({"v1"})
     * @Parameters({
     *     @Parameter(
     *          "page",
     *          type="integer",
     *          required=false,
     *          description="Pagination page",
     *          default=1
     *     ),
     *     @Parameter(
     *          "limit",
     *          type="integer",
     *          required=false,
     *          description="Items per page, set to 0, to return all items",
     *          default=10
     *     ),
     * })
     * @Request(headers={"Accept": "application/x.StarCitizenWikiApi.v1+json"})
     * @Response(200, body={
     * "data": {
     *  {
     *   "funds": "335901302.36",
     *   "fans": 2910921,
     *   "fleet": 2910921,
     *   "timestamp": "2020-12-06T19:00:55.000000Z"
     *  },
     *  {
     *   "funds": "335700500.89",
     *   "fans": 2909995,
     *   "fleet": 2909995,
     *   "timestamp": "2020-12-05T19:00:46.000000Z"
     *  },
     *  {
     *   "funds": "...",
     *  },
     * },
     * "meta": {
     *  "processed_at": "2020-12-07 13:11:53",
     *  "pagination": {
     *      "total": 2903,
     *      "count": 10,
     *      "per_page": 10,
     *      "current_page": 1,
     *      "total_pages": 291,
     *      "links": {
     *          "next": "https:\/\/api.star-citizen.wiki\/api\/stats?page=2"
     *      }
     *  }
     *  }
     * })
     *
     * @return Response
     */
    public function index(): Response
    {
        $stats = Stat::query()->orderByDesc('created_at');

        return $this->getResponse($stats);
    }
}
