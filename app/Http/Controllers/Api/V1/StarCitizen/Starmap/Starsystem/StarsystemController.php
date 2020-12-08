<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\StarCitizen\Starmap\Starsystem;

use App\Http\Controllers\Api\AbstractApiController as ApiController;
use App\Models\Api\StarCitizen\Starmap\Starsystem\Starsystem;
use App\Transformers\Api\V1\StarCitizen\Starmap\StarsystemLinkTransformer;
use App\Transformers\Api\V1\StarCitizen\Starmap\StarsystemTransformer;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Star System API
 * Systems from the Starmap
 *
 * @Resource("Starsystems", uri="/starmap/starsystems")
 */
class StarsystemController extends ApiController
{
    /**
     * StarsystemController constructor.
     *
     * @param Request               $request
     * @param StarsystemTransformer $transformer
     */
    public function __construct(Request $request, StarsystemTransformer $transformer)
    {
        $this->transformer = $transformer;
        parent::__construct($request);
    }

    /**
     * All available Star Systems
     *
     * @Get("/")
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
     *          "include",
     *          type="string",
     *          required=false,
     *          description="Relations to include. Valid relations are shown in the meta data"
     *     ),
     * })
     *
     * @Transaction({
     * @Request(headers={"Accept": "application/x.StarCitizenWikiApi.v1+json"}),
     * @Response(200, body={"data": {{"id": 398,"code": "AYR'KA","system_api_url": "http:\/\/api\/api\/starmap\/starsystems\/AYR'KA","name": "Ail'ka","status": "P","type": "SINGLE_STAR","position": {"x": 139.16,"y": -7.99,"z": 39.58},"frost_line": 179.1,"habitable_zone_inner": 34.37,"habitable_zone_outer": 174.7,"info_url": null,"description": {"de_DE": "...","en_EN": "..."},"aggregated": {"size": 198.97,"population": 8.8,"economy": 3.95,"danger": 0},"time_modified": "2018-11-14 19:51:33","affiliation": {"data": {{"id": 4,"name": "Xi'An","code": "XIAN","color": "#52c231"}}}},{"..."}},"meta": {"processed_at": "2020-12-08 20:37:11","valid_relations": {"jumppoints","celestial_objects"},"pagination": {"total": 90,"count": 15,"per_page": 15,"current_page": 1,"total_pages": 6,"links": {"next": "http:\/\/localhost:8000\/api\/starmap\/starsystems?page=2"}}}}),
     * })
     *
     * @param Request $request
     *
     * @return Response
     */
    public function index(Request $request): Response
    {
        if ($request->has('transformer') && $request->get('transformer', null) === 'link') {
            $this->transformer = new StarsystemLinkTransformer();
        }

        return $this->getResponse(Starsystem::query()->orderBy('name'));
    }

    /**
     * A singular Star System
     *
     * @Get("/{CODE}")
     * @Versions({"v1"})
     * @Parameters({
     *     @Parameter("CODE", type="string", required=true, description="Star System Code or ID"),
     *     @Parameter(
     *          "include",
     *          type="string",
     *          required=false,
     *          description="Relations to include. Valid relations are shown in the meta data"
     *     ),
     * })
     *
     * @Transaction({
     * @Request({"CODE": "SOL"}, headers={"Accept": "application/x.StarCitizenWikiApi.v1+json"}),
     * @Response(200, body={"data": {"id": 355,"code": "SOL","system_api_url": "http:\/\/api\/api\/starmap\/starsystems\/SOL","name": "Sol","status": "P","type": "SINGLE_STAR","position": {"x": 0,"y": 0,"z": 0},"frost_line": 5,"habitable_zone_inner": 0.9,"habitable_zone_outer": 3,"info_url": null,"description": {"de_DE": "...","en_EN": "..."},"aggregated": {"size": 51,"population": 8.59,"economy": 5.58,"danger": 0},"time_modified": "2015-10-10 14:09:45","affiliation": {"data": {{"id": 1,"name": "UEE","code": "uee","color": "#48bbd4"}}}},"meta": {"processed_at": "2020-12-08 20:40:13","valid_relations": {"jumppoints","celestial_objects"}}}),
     * })
     *
     * @param string|int $code
     *
     * @return Response
     */
    public function show($code): Response
    {
        $code = mb_strtoupper(urldecode($code));

        try {
            /** @var Starsystem $starsystem */
            $starsystem = Starsystem::query()
                ->where('code', $code)
                ->orWhere('cig_id', $code)
                ->orWhere('name', 'LIKE', "%$code%")
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            $this->response->errorNotFound(sprintf(static::NOT_FOUND_STRING, $code));
        }

        return $this->getResponse($starsystem);
    }

    /**
     * Search Endpoint
     *
     * @return Response
     */
    public function search(): Response
    {
        $query = $this->request->get('query', '');
        $query = urldecode($query);
        $queryBuilder = Starsystem::query()->where('name', 'like', "%{$query}%");

        if ($queryBuilder->count() === 0) {
            $this->response->errorNotFound(sprintf(static::NOT_FOUND_STRING, $query));
        }

        return $this->getResponse($queryBuilder);
    }
}
