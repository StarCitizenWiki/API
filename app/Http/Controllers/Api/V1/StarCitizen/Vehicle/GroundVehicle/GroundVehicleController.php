<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\StarCitizen\Vehicle\GroundVehicle;

use App\Http\Controllers\Api\AbstractApiController as ApiController;
use App\Http\Requests\StarCitizen\Vehicle\GroundVehicleSearchRequest;
use App\Models\StarCitizen\Vehicle\GroundVehicle\GroundVehicle;
use App\Transformers\Api\V1\StarCitizen\Vehicle\GroundVehicle\GroundVehicleLinkTransformer;
use App\Transformers\Api\V1\StarCitizen\Vehicle\GroundVehicle\GroundVehicleTransformer;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Ground Vehicle API
 * All Vehicles found in the official [Ship Matrix](https://robertsspaceindustries.com/ship-matrix).
 *
 * @Resource("Vehicles", uri="/vehicles")
 */
class GroundVehicleController extends ApiController
{
    /**
     * ShipController constructor.
     *
     * @param GroundVehicleTransformer $transformer
     * @param Request                  $request
     */
    public function __construct(GroundVehicleTransformer $transformer, Request $request)
    {
        $this->transformer = $transformer;

        parent::__construct($request);
    }

    /**
     * Index of all ground vehicles
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
     *     "id": 183,
     *     "chassis_id": 75,
     *     "name": "Anvil Ballista ",
     *     "slug": "anvil-ballista",
     *     "sizes": {"length": 17,"beam": 7,"height": 5.5},
     *     "mass": 0,
     *     "cargo_capacity": 0,
     *     "crew": {"min": 1,"max": 2},
     *     "speed": {"scm": 33},
     *     "foci": {{"de_DE": "Militär","en_EN": "Military"}},
     *     "production_status": {"de_DE": "Flugbereit","en_EN": "flight-ready"},
     *     "production_note": {"de_DE": "Keine","en_EN": "None"},
     *     "type": {"de_DE": "Gefecht","en_EN": "combat"},
     *     "description": {},
     *     "size": {"de_DE": "Fahrzeug","en_EN": "vehicle"},
     *     "manufacturer": {"code": "ANVL","name": "Anvil Aerospace"},
     *     "updated_at": "2020-11-20T00:49:52.000000Z",
     *     "missing_translations": {"de_DE","anvil-ballista"}
     *     },
     *     {"id": "..."}
     *     },
     *     "meta": {
     *     "processed_at": "2020-12-08 20:32:51",
     *     "valid_relations": {"components"},
     *     "pagination": {
     *     "total": 17,
     *     "count": 5,
     *     "per_page": 5,
     *     "current_page": 1,
     *     "total_pages": 4,
     *     "links": {"next": "https:\/\/api.star-citizen.wiki\/api\/vehicles?page=2"}}}}),
     * })
     *
     * @param Request $request
     *
     * @return Response
     */
    public function index(Request $request): Response
    {
        if ($request->has('transformer') && $request->get('transformer', null) === 'link') {
            $this->transformer = new GroundVehicleLinkTransformer();
        }

        return $this->getResponse(GroundVehicle::query()->orderBy('name'));
    }

    /**
     * Single vehicle
     * Output of a single vehicle by vehicle name or slug (e.g. Cyclone)
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
     * @Request({"NAME": "Cyclone"}, headers={"Accept": "application/x.StarCitizenWikiApi.v1+json"}),
     * @Response(200, body={
     *     "data": {
     *     "id": 134,
     *     "chassis_id": 53,
     *     "name": "Cyclone",
     *     "slug": "cyclone",
     *     "sizes": {"length": 6,"beam": 4,"height": 2.5},
     *     "mass": 3022,
     *     "cargo_capacity": 1,
     *     "crew": {"min": 1,"max": 2},
     *     "speed": {"scm": 0},
     *     "foci": {{"de_DE": "Erkundung","en_EN": "Exploration"},{"de_DE": "Aufklärung","en_EN": "Recon"}},
     *     "production_status": {"de_DE": "Flugbereit","en_EN": "flight-ready"},
     *     "production_note": {"de_DE": "Keine","en_EN": "None"},
     *     "type": {"de_DE": "Gelände","en_EN": "ground"},
     *     "description": {"de_DE": "...","en_EN": "..."},
     *     "size": {"de_DE": "Fahrzeug","en_EN": "vehicle"},
     *     "manufacturer": {"code": "TMBL","name": "Tumbril"},
     *     "updated_at": "2019-11-10T17:40:17.000000Z",
     *     "missing_translations": {}
     *     },"meta": {"processed_at": "2020-12-08 20:31:53","valid_relations": {"components"}}}),
     *
     * @Request({"NAME": "Cyclone", "locale": "de_DE"}, headers={"Accept": "application/x.StarCitizenWikiApi.v1+json"}),
     * @Response(200, body={
     *     "data": {
     *     "id": 134,
     *     "chassis_id": 53,
     *     "name": "Cyclone",
     *     "slug": "cyclone",
     *     "sizes": {"length": 6,"beam": 4,"height": 2.5},
     *     "mass": 3022,
     *     "cargo_capacity": 1,
     *     "crew": {"min": 1,"max": 2},
     *     "speed": {"scm": 0},
     *     "foci": {"Erkundung", "Aufklärung"},
     *     "production_status": "Flugbereit",
     *     "production_note": "Keine",
     *     "type": "Gelände",
     *     "description": "...",
     *     "size": "Fahrzeug",
     *     "manufacturer": {"code": "TMBL","name": "Tumbril"},
     *     "updated_at": "2019-11-10T17:40:17.000000Z",
     *     "missing_translations": {}
     *     },
     *     "meta": {"processed_at": "2020-12-08 20:29:47","valid_relations": {"components"}}}),
     *
     * @Request({"NAME": "Cyclone", "locale": "de_DE", "include": "components"},
     *     headers={"Accept": "application/x.StarCitizenWikiApi.v1+json"}),
     * @Response(200, body={
     *     "data": {
     *     "id": 134,
     *     "chassis_id": 53,
     *     "name": "Cyclone",
     *     "slug": "cyclone",
     *     "sizes": {"length": 6,"beam": 4,"height": 2.5},
     *     "mass": 3022,
     *     "cargo_capacity": 1,
     *     "crew": {"min": 1,"max": 2},
     *     "speed": {"scm": 0},
     *     "foci": {"Erkundung", "Aufklärung"},
     *     "production_status": "Flugbereit",
     *     "production_note": "Keine",
     *     "type": "Gelände",
     *     "description": "...",
     *     "size": "Fahrzeug",
     *     "manufacturer": {"code": "TMBL","name": "Tumbril"},
     *     "updated_at": "2019-11-10T17:40:17.000000Z",
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
     *
     * @param Request $request
     *
     * @return Response
     * @throws ValidationException
     */
    public function show(Request $request): Response
    {
        ['ground_vehicle' => $groundVehicle] = Validator::validate(
            [
                'ground_vehicle' => $request->ground_vehicle,
            ],
            [
                'ground_vehicle' => 'required|string|min:1|max:255',
            ]
        );

        $groundVehicle = urldecode($groundVehicle);

        try {
            $groundVehicle = GroundVehicle::query()
                ->where('name', $groundVehicle)
                ->orWhere('slug', $groundVehicle)
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            $this->response->errorNotFound(sprintf(static::NOT_FOUND_STRING, $groundVehicle));
        }

        return $this->getResponse($groundVehicle);
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
        $rules = (new GroundVehicleSearchRequest())->rules();

        $request->validate($rules);

        $query = urldecode($request->get('query'));
        $queryBuilder = GroundVehicle::query()
            ->where('name', 'like', "%{$query}%")
            ->orWhere('slug', 'like', "%{$query}%");

        if ($queryBuilder->count() === 0) {
            $this->response->errorNotFound(sprintf(static::NOT_FOUND_STRING, $query));
        }

        return $this->getResponse($queryBuilder);
    }
}
