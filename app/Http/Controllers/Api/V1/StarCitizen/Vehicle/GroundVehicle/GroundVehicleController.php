<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\StarCitizen\Vehicle\GroundVehicle;

use App\Http\Controllers\Api\AbstractApiController as ApiController;
use App\Http\Requests\StarCitizen\Vehicle\GroundVehicleSearchRequest;
use App\Models\Api\StarCitizen\Vehicle\GroundVehicle\GroundVehicle;
use App\Transformers\Api\V1\StarCitizen\Vehicle\GroundVehicle\GroundVehicleLinkTransformer;
use App\Transformers\Api\V1\StarCitizen\Vehicle\GroundVehicle\GroundVehicleTransformer;
use App\Transformers\Api\V1\StarCitizen\Vehicle\Ship\ShipLinkTransformer;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Ground Vehicle API
 * Output of the ground vehicles of the Ship Matrix
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
     * All ground vehicles
     * Output of all ground vehicles of the Ship Matrix paginated
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
     * @Response(200, body={"data": {{"id": 183,"chassis_id": 75,"name": "Anvil Ballista ","slug": "anvil-ballista","sizes": {"length": 17,"beam": 7,"height": 5.5},"mass": 0,"cargo_capacity": 0,"crew": {"min": 1,"max": 2},"speed": {"scm": 33},"foci": {{"de_DE": "Militär","en_EN": "Military"}},"production_status": {"de_DE": "Flugbereit","en_EN": "flight-ready"},"production_note": {"de_DE": "Keine","en_EN": "None"},"type": {"de_DE": "Gefecht","en_EN": "combat"},"description": {},"size": {"de_DE": "Fahrzeug","en_EN": "vehicle"},"manufacturer": {"code": "ANVL","name": "Anvil Aerospace"},"updated_at": "2020-11-20T00:49:52.000000Z","missing_translations": {"de_DE","anvil-ballista"}},{"..."}},"meta": {"processed_at": "2020-12-08 20:32:51","valid_relations": {"components"},"pagination": {"total": 17,"count": 5,"per_page": 5,"current_page": 1,"total_pages": 4,"links": {"next": "http:\/\/localhost:8000\/api\/vehicles?page=2"}}}}),
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
     * Single ground vehicle
     * Output of a single ground vehicle by vehicle name (e.g. Cyclone)
     * Name of ground vehicle should be URL encoded
     *
     * @Get("/{NAME}")
     * @Versions({"v1"})
     * @Parameters({
     *     @Parameter("NAME", type="string", required=true, description="Vehicle Name or Slug"),
     *     @Parameter(
     *          "include",
     *          type="string",
     *          required=false,
     *          description="Relations to include. Valid relations are shown in the meta data"
     *     ),
     * })
     *
     * @Transaction({
     * @Request({"NAME": "Cyclone"}, headers={"Accept": "application/x.StarCitizenWikiApi.v1+json"}),
     * @Response(200, body={"data": {"id": 134,"chassis_id": 53,"name": "Cyclone","slug": "cyclone","sizes": {"length": 6,"beam": 4,"height": 2.5},"mass": 3022,"cargo_capacity": 1,"crew": {"min": 1,"max": 2},"speed": {"scm": 0},"foci": {{"de_DE": "Erkundung","en_EN": "Exploration"},{"de_DE": "Aufklärung","en_EN": "Recon"}},"production_status": {"de_DE": "Flugbereit","en_EN": "flight-ready"},"production_note": {"de_DE": "Keine","en_EN": "None"},"type": {"de_DE": "Gelände","en_EN": "ground"},"description": {"de_DE": "...","en_EN": "..."},"size": {"de_DE": "Fahrzeug","en_EN": "vehicle"},"manufacturer": {"code": "TMBL","name": "Tumbril"},"updated_at": "2019-11-10T17:40:17.000000Z","missing_translations": {}},"meta": {"processed_at": "2020-12-08 20:31:53","valid_relations": {"components"}}}),
     * })
     *
     * @param string $groundVehicle
     *
     * @return Response
     */
    public function show(string $groundVehicle): Response
    {
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
