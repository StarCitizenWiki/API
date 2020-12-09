<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\StarCitizen\Starmap\Starsystem;

use App\Http\Controllers\Api\AbstractApiController as ApiController;
use App\Http\Requests\StarCitizen\Starmap\StarsystemRequest;
use App\Models\Api\StarCitizen\Starmap\Starsystem\Starsystem;
use App\Transformers\Api\V1\StarCitizen\Starmap\StarsystemLinkTransformer;
use App\Transformers\Api\V1\StarCitizen\Starmap\StarsystemTransformer;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Star System API
 * Systems from the official [Starmap](https://robertsspaceindustries.com/starmap).
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
     * Index of all available Star Systems
     *
     * @Get("/{?page,locale,include,limit}")
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
     *          description="Relations to include. Valid relations are listed in the meta data"
     *     ),
     *     @Parameter(
     *          "locale",
     *          type="string",
     *          required=false,
     *          description="Localization to use. Supported codes: 'de_DE', 'en_EN'"
     *     ),
     *     @Parameter(
     *          "limit",
     *          type="integer",
     *          required=false,
     *          description="Items per page, set to 0, to return all items",
     *          default=10
     *     ),
     * })
     *
     * @Transaction({
     * @Request(headers={"Accept": "application/x.StarCitizenWikiApi.v1+json"}),
     * @Response(200, body={
     *     "data": {
     *     {
     *     "id": 398,
     *     "code": "AYR'KA",
     *     "system_api_url": "https:\/\/api.star-citizen.wiki\/api\/starmap\/starsystems\/AYR'KA",
     *     "name": "Ail'ka",
     *     "status": "P",
     *     "type": "SINGLE_STAR",
     *     "position": {"x": 139.16,"y": -7.99,"z": 39.58},
     *     "frost_line": 179.1,
     *     "habitable_zone_inner": 34.37,
     *     "habitable_zone_outer": 174.7,
     *     "info_url": null,
     *     "description": {"de_DE": "...","en_EN": "..."},
     *     "aggregated": {"size": 198.97,"population": 8.8,"economy": 3.95,"danger": 0},
     *     "updated_at": "2020-10-15T13:19:19.000000Z",
     *     "affiliation": {
     *     "data": {{"id": 4,"name": "Xi'An","code": "XIAN","color": "#52c231"}}
     *     }},
     *     {"id": "..."}},
     *     "meta": {"processed_at": "2020-12-08 20:37:11",
     *     "valid_relations": {"jumppoints","celestial_objects"},
     *     "pagination": {"total": 90,"count": 15,"per_page": 15,"current_page": 1,"total_pages": 6,
     *     "links": {"next": "https:\/\/api.star-citizen.wiki\/api\/starmap\/starsystems?page=2"}}}}),
     *
     * @Request({"include": "jumppoints"}, headers={"Accept": "application/x.StarCitizenWikiApi.v1+json"}),
     * @Response(200, body={
     *     "data": {
     *     {
     *     "id": 398,
     *     "code": "AYR'KA",
     *     "system_api_url": "https:\/\/api.star-citizen.wiki\/api\/starmap\/starsystems\/AYR'KA",
     *     "name": "Ail'ka",
     *     "status": "P",
     *     "type": "SINGLE_STAR",
     *     "position": {"x": 139.16,"y": -7.99,"z": 39.58},
     *     "frost_line": 179.1,
     *     "habitable_zone_inner": 34.37,
     *     "habitable_zone_outer": 174.7,
     *     "info_url": null,
     *     "description": {"de_DE": "...","en_EN": "..."},
     *     "aggregated": {"size": 198.97,"population": 8.8,"economy": 3.95,"danger": 0},
     *     "updated_at": "2020-10-15T13:19:19.000000Z",
     *     "affiliation": {
     *     "data": {{"id": 4,"name": "Xi'An","code": "XIAN","color": "#52c231"}}
     *     },
     *     "jumppoints": {
     *       "data": {
     *         {
     *           "id": 1341,
     *           "size": "L",
     *           "direction": "B",
     *           "entry": {
     *             "id": 2175,
     *             "system_id": 377,
     *             "system_api_url": "https:\/\/api.star-citizen.wiki\/api\/starmap\/starsystems\/377",
     *             "celestial_object_api_url":
     *             "https:\/\/api.star-citizen.wiki\/api\/starmap\/celestial-objects\/HADUR.JUMPPOINTS.AYR'KA",
     *             "status": "P",
     *             "code": "HADUR.JUMPPOINTS.AYR'KA",
     *             "designation": "Yā’mon (Hadur) - Ail'ka"
     *           },
     *           "exit": {
     *             "id": 2270,
     *             "system_id": 398,
     *             "system_api_url": "https:\/\/api.star-citizen.wiki\/api\/starmap\/starsystems\/398",
     *             "celestial_object_api_url":
     *             "https:\/\/api.star-citizen.wiki\/api\/starmap\/celestial-objects\/AYR'KA.JUMPPOINTS.HADUR",
     *             "status": "P",
     *             "code": "AYR'KA.JUMPPOINTS.HADUR",
     *             "designation": "Ail'ka - Yā’mon (Hadur)"
     *           }
     *         },
     *         {
     *           "id": 1378,
     *           "size": "L",
     *           "direction": "B",
     *           "entry": {
     *             "id": 2269,
     *             "system_id": 398,
     *             "system_api_url": "https:\/\/api.star-citizen.wiki\/api\/starmap\/starsystems\/398",
     *             "celestial_object_api_url":
     *             "https:\/\/api.star-citizen.wiki\/api\/starmap\/celestial-objects\/AYR'KA.JUMPPOINTS.INDRA",
     *             "status": "P",
     *             "code": "AYR'KA.JUMPPOINTS.INDRA",
     *             "designation": "Ail'ka - Kyuk’ya (Indra)"
     *           },
     *           "exit": {
     *             "id": 2274,
     *             "system_id": 399,
     *             "system_api_url": "https:\/\/api.star-citizen.wiki\/api\/starmap\/starsystems\/399",
     *             "celestial_object_api_url":
     *             "https:\/\/api.star-citizen.wiki\/api\/starmap\/celestial-objects\/INDRA.JUMPPOINTS.AYR'KA",
     *             "status": "P",
     *             "code": "INDRA.JUMPPOINTS.AYR'KA",
     *             "designation": "Kyuk’ya (Indra) - Ail'ka"
     *           }
     *         }
     *         }
     *       },
     *     },
     *     {"id": "..."}},
     *     "meta": {"processed_at": "2020-12-08 20:37:11",
     *     "valid_relations": {"jumppoints","celestial_objects"},
     *     "pagination": {"total": 90,"count": 15,"per_page": 15,"current_page": 1,"total_pages": 6,
     *     "links": {"next": "https:\/\/api.star-citizen.wiki\/api\/starmap\/starsystems?page=2"}}}}),
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
     * @Get("/{CODE}{?locale,include}")
     * @Versions({"v1"})
     * @Parameters({
     *     @Parameter("CODE", type="string", required=true, description="Star System Code or ID"),
     *     @Parameter(
     *          "include",
     *          type="string",
     *          required=false,
     *          description="Relations to include. Valid relations are listed in the meta data"
     *     ),
     *     @Parameter(
     *          "locale",
     *          type="string",
     *          required=false,
     *          description="Localization to use. Supported codes: 'de_DE', 'en_EN'"
     *     ),
     * })
     *
     * @Transaction({
     * @Request({"CODE": "SOL"}, headers={"Accept": "application/x.StarCitizenWikiApi.v1+json"}),
     * @Response(200, body={
     *     "data": {
     *     "id": 355,
     *     "code": "SOL",
     *     "system_api_url": "https:\/\/api.star-citizen.wiki\/api\/starmap\/starsystems\/SOL",
     *     "name": "Sol",
     *     "status": "P",
     *     "type": "SINGLE_STAR",
     *     "position": {"x": 0,"y": 0,"z": 0},
     *     "frost_line": 5,
     *     "habitable_zone_inner": 0.9,
     *     "habitable_zone_outer": 3,
     *     "info_url": null,
     *     "description": {"de_DE": "...","en_EN": "..."},
     *     "aggregated": {"size": 51,"population": 8.59,"economy": 5.58,"danger": 0},
     *     "updated_at": "2020-10-15T13:19:19.000000Z",
     *     "affiliation": {"data": {{"id": 1,"name": "UEE","code": "uee","color": "#48bbd4"}}}},
     *     "meta": {"processed_at": "2020-12-08 20:40:13","valid_relations": {"jumppoints","celestial_objects"}}}),
     * })
     *
     * @param Request $request
     *
     * @return Response
     */
    public function show(Request $request): Response
    {
        $request->validate(
            [
                'code' => 'required|string|min:1|max:255',
            ]
        );

        $code = mb_strtoupper(urldecode($request->get('code')));

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
     * @param Request $request
     *
     * @return Response
     */
    public function search(Request $request): Response
    {
        $rules = (new StarsystemRequest())->rules();

        $request->validate($rules);

        $query = urldecode($this->request->get('query', ''));
        $queryBuilder = Starsystem::query()->where('name', 'like', "%{$query}%");

        if ($queryBuilder->count() === 0) {
            $this->response->errorNotFound(sprintf(static::NOT_FOUND_STRING, $query));
        }

        return $this->getResponse($queryBuilder);
    }
}
