<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\StarCitizen\Vehicle\Ship;

use App\Http\Controllers\Api\AbstractApiController as ApiController;
use App\Http\Requests\StarCitizen\Vehicle\ShipSearchRequest;
use App\Http\Requests\StarCitizen\Vehicle\StarsystemRequest;
use App\Models\Api\StarCitizen\Vehicle\Ship\Ship;
use App\Transformers\Api\V1\StarCitizen\Vehicle\Ship\ShipLinkTransformer;
use App\Transformers\Api\V1\StarCitizen\Vehicle\Ship\ShipTransformer;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Ship API
 * All Ships found in the official [Ship Matrix](https://robertsspaceindustries.com/ship-matrix).
 *
 * @Resource("Ships", uri="/ships")
 */
class ShipController extends ApiController
{
    /**
     * ShipController constructor.
     *
     * @param ShipTransformer $transformer
     * @param Request         $request
     */
    public function __construct(ShipTransformer $transformer, Request $request)
    {
        $this->transformer = $transformer;

        parent::__construct($request);
    }

    /**
     * Index of all ships
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
     * @Response(200, body={"data": {
     *     {
     *     "id": 159,
     *     "chassis_id": 63,
     *     "name": "100i",
     *     "slug": "100i",
     *     "sizes": {"length": 19.3,"beam": 11,"height": 4},
     *     "mass": 0,
     *     "cargo_capacity": 2,
     *     "crew": {"min": 1,"max": 1},
     *     "speed": {"scm": 210,"afterburner": 0},
     *     "agility": {"pitch": 0,"yaw": 0,"roll": 0,
     *     "acceleration": {"x_axis": 0,"y_axis": 0,"z_axis": 0}},
     *     "foci": {{"de_DE": "Einsteiger","en_EN": "Starter"},{"de_DE": "Reisen","en_EN": "Touring"}},
     *     "production_status": {"de_DE": "Flugbereit","en_EN": "flight-ready"},
     *     "production_note": {"de_DE": "Keine","en_EN": "None"},
     *     "type": {"de_DE": "Mehrzweck","en_EN": "multi"},
     *     "description": {"de_DE": "....","en_EN": "..."},
     *     "size": {"de_DE": "Klein","en_EN": "small"},
     *     "manufacturer": {"code": "ORIG","name": "Origin Jumpworks GmbH"},
     *     "updated_at": "2020-10-15T13:19:19.000000Z",
     *     "missing_translations": {}
     *     },
     *     {"id": "..."}
     *     },
     *     "meta": {
     *     "processed_at": "2020-12-08 20:27:04",
     *     "valid_relations": {"components"},
     *     "pagination": {
     *     "total": 154,
     *     "count": 5,
     *     "per_page": 5,
     *     "current_page": 1,
     *     "total_pages": 31,
     *     "links": {"next": "https:\/\/api.star-citizen.wiki\/api\/ships?page=2"}}}}),
     * })
     *
     * @param Request $request
     *
     * @return Response
     */
    public function index(Request $request): Response
    {
        if ($request->has('transformer') && $request->get('transformer', null) === 'link') {
            $this->transformer = new ShipLinkTransformer();
        }

        return $this->getResponse(Ship::query()->orderBy('name'));
    }

    /**
     * Single ship
     * Output of a single ship by name or slug (e.g. 3001)
     *
     * @Get("/{NAME}{?locale,include}")
     * @Versions({"v1"})
     * @Parameters({
     *     @Parameter("NAME", type="string", required=true, description="URL encoded Name or Slug"),
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
     * @Request({"NAME": "100i"}, headers={"Accept": "application/x.StarCitizenWikiApi.v1+json"}),
     * @Response(200, body={
     *     "data": {
     *     "id": 159,
     *     "chassis_id": 63,
     *     "name": "100i",
     *     "slug": "100i",
     *     "sizes": {"length": 19.3,"beam": 11,"height": 4},
     *     "mass": 0,
     *     "cargo_capacity": 2,
     *     "crew": {"min": 1,"max": 1},
     *     "speed": {"scm": 210,"afterburner": 0},
     *     "agility": {"pitch": 0,"yaw": 0,"roll": 0,"acceleration": {"x_axis": 0,"y_axis": 0,"z_axis": 0}},
     *     "foci": {{"de_DE": "Einsteiger","en_EN": "Starter"},{"de_DE": "Reisen","en_EN": "Touring"}},
     *     "production_status": {"de_DE": "Flugbereit","en_EN": "flight-ready"},
     *     "production_note": {"de_DE": "Keine","en_EN": "None"},
     *     "type": {"de_DE": "Mehrzweck","en_EN": "multi"},
     *     "description": {"de_DE": "...","en_EN": "..."},
     *     "size": {"de_DE": "Klein","en_EN": "small"},
     *     "manufacturer": {"code": "ORIG","name": "Origin Jumpworks GmbH"},
     *     "updated_at": "2020-10-15T13:19:19.000000Z",
     *     "missing_translations": {}
     *     },
     *     "meta": {"processed_at": "2020-12-08 20:29:47","valid_relations": {"components"}}}),
     *
     * @Request({"NAME": "100i", "locale": "de_DE"}, headers={"Accept": "application/x.StarCitizenWikiApi.v1+json"}),
     * @Response(200, body={
     *     "data": {
     *     "id": 159,
     *     "chassis_id": 63,
     *     "name": "100i",
     *     "slug": "100i",
     *     "sizes": {"length": 19.3,"beam": 11,"height": 4},
     *     "mass": 0,
     *     "cargo_capacity": 2,
     *     "crew": {"min": 1,"max": 1},
     *     "speed": {"scm": 210,"afterburner": 0},
     *     "agility": {"pitch": 0,"yaw": 0,"roll": 0,"acceleration": {"x_axis": 0,"y_axis": 0,"z_axis": 0}},
     *     "foci": {"Einsteiger", "Reisen"},
     *     "production_status": "Flugbereit",
     *     "production_note": "Keine",
     *     "type": "Mehrzweck",
     *     "description": "...",
     *     "size": "Klein",
     *     "manufacturer": {"code": "ORIG","name": "Origin Jumpworks GmbH"},
     *     "updated_at": "2020-10-15T13:19:19.000000Z",
     *     "missing_translations": {}
     *     },
     *     "meta": {"processed_at": "2020-12-08 20:29:47","valid_relations": {"components"}}}),
     *
     * @Request({"NAME": "100i", "locale": "de_DE", "include": "components"},
     *     headers={"Accept": "application/x.StarCitizenWikiApi.v1+json"}),
     * @Response(200, body={
     *     "data": {
     *     "id": 159,
     *     "chassis_id": 63,
     *     "name": "100i",
     *     "slug": "100i",
     *     "sizes": {"length": 19.3,"beam": 11,"height": 4},
     *     "mass": 0,
     *     "cargo_capacity": 2,
     *     "crew": {"min": 1,"max": 1},
     *     "speed": {"scm": 210,"afterburner": 0},
     *     "agility": {"pitch": 0,"yaw": 0,"roll": 0,"acceleration": {"x_axis": 0,"y_axis": 0,"z_axis": 0}},
     *     "foci": {"Einsteiger", "Reisen"},
     *     "production_status": "Flugbereit",
     *     "production_note": "Keine",
     *     "type": "Mehrzweck",
     *     "description": "...",
     *     "size": "Klein",
     *     "manufacturer": {"code": "ORIG","name": "Origin Jumpworks GmbH"},
     *     "updated_at": "2020-10-15T13:19:19.000000Z",
     *     "missing_translations": {},
     *     "components": {
     *     "data": {
     *     {
     *     "type": "radar",
     *     "name": "Radar",
     *     "mounts": 1,
     *     "component_size": "S",
     *     "category": "",
     *     "size": "S",
     *     "details": "",
     *     "quantity": 1,
     *     "manufacturer": "TBD",
     *     "component_class": "RSIAvionic"
     *     },
     *     {"type": "..."},
     *     }
     *     }
     *     },
     *     "meta": {"processed_at": "2020-12-08 20:29:47","valid_relations": {"components"}}}),
     *
     * @Request({"NAME": "invalid"}, headers={"Accept": "application/x.StarCitizenWikiApi.v1+json"}),
     * @Response(404, body={
     *     "message": "No Results for Query 'invalid'",
     *     "status_code": 404
     * }),
     * })
     *
     * @param Request $request
     *
     * @return Response
     * @throws ValidationException
     */
    public function show(Request $request): Response
    {
        ['ship' => $ship] = Validator::validate(
            [
                'ship' => $request->ship,
            ],
            [
                'ship' => 'required|string|min:1|max:255',
            ]
        );

        $ship = urldecode($ship);

        try {
            $ship = Ship::query()
                ->where('name', $ship)
                ->orWhere('slug', $ship)
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            $this->response->errorNotFound(sprintf(static::NOT_FOUND_STRING, $ship));
        }

        return $this->getResponse($ship);
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
        $rules = (new ShipSearchRequest())->rules();

        $request->validate($rules);

        $query = urldecode($request->get('query'));
        $queryBuilder = Ship::query()
            ->where('name', 'like', "%{$query}%")
            ->orWhere('slug', 'like', "%{$query}%");

        if ($queryBuilder->count() === 0) {
            $this->response->errorNotFound(sprintf(static::NOT_FOUND_STRING, $query));
        }

        return $this->getResponse($queryBuilder);
    }
}
