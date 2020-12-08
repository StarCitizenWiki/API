<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\StarCitizen\Vehicle\Ship;

use App\Http\Controllers\Api\AbstractApiController as ApiController;
use App\Http\Requests\StarCitizen\Vehicle\ShipSearchRequest;
use App\Models\Api\StarCitizen\Vehicle\Ship\Ship;
use App\Transformers\Api\V1\StarCitizen\Vehicle\Ship\ShipLinkTransformer;
use App\Transformers\Api\V1\StarCitizen\Vehicle\Ship\ShipTransformer;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Spaceship API
 * Output of the spaceships of the Ship Matrix
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
     * All spaceships
     * Output of all spaceships of the Ship Matrix paginated
     *
     * // phpcs:disable
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
     * @Response(200, body={"data": {{"id": 159,"chassis_id": 63,"name": "100i","slug": "100i","sizes": {"length": 19.3,"beam": 11,"height": 4},"mass": 0,"cargo_capacity": 2,"crew": {"min": 1,"max": 1},"speed": {"scm": 210,"afterburner": 0},"agility": {"pitch": 0,"yaw": 0,"roll": 0,"acceleration": {"x_axis": 0,"y_axis": 0,"z_axis": 0}},"foci": {{"de_DE": "Einsteiger","en_EN": "Starter"},{"de_DE": "Reisen","en_EN": "Touring"}},"production_status": {"de_DE": "Flugbereit","en_EN": "flight-ready"},"production_note": {"de_DE": "Keine","en_EN": "None"},"type": {"de_DE": "Mehrzweck","en_EN": "multi"},"description": {"de_DE": "Tour durch das Universum mit der perfekten Verbindung von Luxus und Leistung. Die 100i verfügt über das patentierte AIR-Kraftstoffsystem von Origin Jumpworks und ist damit das effizienteste und umweltfreundlichste Schiff auf dem Markt. Die 100i ist für Langstreckenflüge geeignet, für die die meisten Schiffe ihrer Größe nicht gerüstet sind, und sie ist perfekt für Solopiloten, die auf sich aufmerksam machen wollen, ohne auf Funktionalität oder Zuverlässigkeit zu verzichten.","en_EN": "Tour the universe with the perfect coupling of luxury and performance. The 100i features Origin Jumpworks' patented AIR fuel system, making it the most efficient and eco-friendly ship on the market. Capable of long distance flights that most ships of its size aren't equipped for, the 100i is perfect for solo pilots looking to turn heads without sacrificing functionality or reliability."},"size": {"de_DE": "Klein","en_EN": "small"},"manufacturer": {"code": "ORIG","name": "Origin Jumpworks GmbH"},"updated_at": "2020-10-15T13:19:19.000000Z","missing_translations": {}},{"...",}},"meta": {"processed_at": "2020-12-08 20:27:04","valid_relations": {"components"},"pagination": {"total": 154,"count": 5,"per_page": 5,"current_page": 1,"total_pages": 31,"links": {"next": "http:\/\/localhost:8000\/api\/ships?page=2"}}}}),
     * })
     * // phpcs:enable
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
     * Single spaceship
     * Output of a single spaceship by ship name (e.g. 300i)
     * Name of the ship should be URL encoded
     *
     * // phpcs:disable
     * @Get("/{NAME}")
     * @Versions({"v1"})
     * @Parameters({
     *     @Parameter("NAME", type="string", required=true, description="Ship Name or Slug"),
     *     @Parameter(
     *          "include",
     *          type="string",
     *          required=false,
     *          description="Relations to include. Valid relations are shown in the meta data"
     *     ),
     * })
     *
     * @Transaction({
     * @Request({"NAME": "100i"}, headers={"Accept": "application/x.StarCitizenWikiApi.v1+json"}),
     * @Response(200, body={"data": {"id": 159,"chassis_id": 63,"name": "100i","slug": "100i","sizes": {"length": 19.3,"beam": 11,"height": 4},"mass": 0,"cargo_capacity": 2,"crew": {"min": 1,"max": 1},"speed": {"scm": 210,"afterburner": 0},"agility": {"pitch": 0,"yaw": 0,"roll": 0,"acceleration": {"x_axis": 0,"y_axis": 0,"z_axis": 0}},"foci": {{"de_DE": "Einsteiger","en_EN": "Starter"},{"de_DE": "Reisen","en_EN": "Touring"}},"production_status": {"de_DE": "Flugbereit","en_EN": "flight-ready"},"production_note": {"de_DE": "Keine","en_EN": "None"},"type": {"de_DE": "Mehrzweck","en_EN": "multi"},"description": {"de_DE": "...","en_EN": "..."},"size": {"de_DE": "Klein","en_EN": "small"},"manufacturer": {"code": "ORIG","name": "Origin Jumpworks GmbH"},"updated_at": "2020-10-15T13:19:19.000000Z","missing_translations": {}},"meta": {"processed_at": "2020-12-08 20:29:47","valid_relations": {"components"}}}),
     * })
     * // phpcs:enable
     *
     * @param string $ship
     *
     * @return Response
     */
    public function show(string $ship): Response
    {
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
