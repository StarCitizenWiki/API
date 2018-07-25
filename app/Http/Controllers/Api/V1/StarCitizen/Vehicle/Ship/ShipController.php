<?php declare(strict_types = 1);

namespace App\Http\Controllers\Api\V1\StarCitizen\Vehicle\Ship;

use App\Http\Controllers\Controller;
use App\Repositories\Api\V1\StarCitizen\Interfaces\Vehicle\Ship\ShipRepositoryInterface;

/**
 * @Resource("Ships", uri="/vehicles/ships")
 */
class ShipController extends Controller
{
    /**
     * @var \App\Repositories\Api\V1\StarCitizen\Interfaces\Vehicle\Ship\ShipRepositoryInterface
     */
    private $repository;

    /**
     * ShipController constructor.
     *
     * @param \App\Repositories\Api\V1\StarCitizen\Interfaces\Vehicle\Ship\ShipRepositoryInterface $shipRepository
     */
    public function __construct(ShipRepositoryInterface $shipRepository)
    {
        $this->repository = $shipRepository;
    }

    /**
     * @param string $shipName
     *
     * @return \Dingo\Api\Http\Response
     *
     * @GET("/{shipName}")
     *
     * @Versions({"v1"})
     *
     * @Response(200, body={
     *     "data": {
     *          "id": 7,
     *          "chassis_id": 2,
     *          "name": "300i",
     *          "sizes": {
     *              "length": 23,
     *              "beam": 15.5,
     *              "height": 7
     *          },
     *          "mass": 65925,
     *          "cargo_capacity": 2,
     *          "crew": {
     *              "min": 1,
     *              "max": 1
     *          },
     *          "speed": {
     *              "scm": 275,
     *              "afterburner": 1190
     *          },
     *          "rotation": {
     *              "pitch": 85,
     *              "yaw": 85,
     *              "roll": 120
     *          },
     *          "acceleration": {
     *              "x_axis": 68,
     *              "y_axis": 80.3,
     *              "z_axis": 71.7
     *          },
     *          "foci": [
     *              {
     *                  "de_DE": "Reisen",
     *                  "en_EN": "Touring"
     *              }
     *          ],
     *          "production_status": {
     *              "de_DE": "Flugbereit",
     *              "en_EN": "flight-ready"
     *          },
     *          "type": {
     *              "de_DE": "Erkundung",
     *              "en_EN": "exploration"
     *          },
     *          "description": {
     *              "de_DE": "[...]",
     *              "en_EN": "If you're going to travel the stars... [...]"
     *          },
     *          "size": {
     *              "de_DE": "Klein",
     *              "en_EN": "small"
     *          },
     *          "manufacturer": {
     *              "code": "ORIG",
     *              "name": "Origin Jumpworks GmbH"
     *          }
     *      }
     * })
     * @Response(404)
     */
    public function show(string $shipName)
    {
        return $this->repository->show($shipName);
    }

    /**
     * @return \Dingo\Api\Http\Response
     *
     * @Get("/")
     *
     * @Versions({"v1"})
     *
     * @Parameters({
     *      @Parameter("page", description="The page of results to view.", default=1)
     * })
     */
    public function index()
    {
        return $this->repository->all();
    }
}
