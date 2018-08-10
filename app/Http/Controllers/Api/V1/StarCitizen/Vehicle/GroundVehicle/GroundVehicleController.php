<?php declare(strict_types = 1);

namespace App\Http\Controllers\Api\V1\StarCitizen\Vehicle\GroundVehicle;

use App\Http\Controllers\Api\AbstractApiController as ApiController;
use App\Models\Api\StarCitizen\Vehicle\GroundVehicle\GroundVehicle;
use App\Transformers\Api\V1\StarCitizen\Vehicle\GroundVehicle\GroundVehicleTransformer;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

/**
 * @Resource("GroundVehicles", uri="/vehicles/ground_vehicles")
 *
 * @covers \App\Http\Controllers\Api\AbstractApiController
 */
class GroundVehicleController extends ApiController
{
    /**
     * ShipController constructor.
     *
     * @param \App\Transformers\Api\V1\StarCitizen\Vehicle\GroundVehicle\GroundVehicleTransformer $transformer
     * @param \Illuminate\Http\Request                                                            $request
     */
    public function __construct(GroundVehicleTransformer $transformer, Request $request)
    {
        $this->transformer = $transformer;
        parent::__construct($request);
    }

    /**
     * @param string $groundVehicle
     *
     * @return \Dingo\Api\Http\Response
     *
     * @GET("/{groundVehicleName}")
     *
     * @Versions({"v1"})
     *
     * @Parameters({
     *      @Parameter("locale", description="The Locale Code to return. Valid values: de_DE, en_EN. Fallback language is en_EN", type="string")
     * })
     *
     * @Response(200, body={
     *     "data": {
     *          "id": 7,
     *          "chassis_id": 2,
     *          "name": "Cyclone",
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
     *              "scm": 20
     *          },
     *          "foci": {
     *              {
     *                  "de_DE": "Reisen",
     *                  "en_EN": "Touring"
     *              }
     *          },
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
     *              "de_DE": "vehicle",
     *              "en_EN": "Fahrzeug"
     *          },
     *          "manufacturer": {
     *              "code": "TMBL",
     *              "name": "Tumbril"
     *          }
     *      }
     * })
     * @Response(200, body={
     *     "data": {
     *          "id": 7,
     *          "chassis_id": 2,
     *          "name": "Cyclone",
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
     *              "scm": 275
     *          },
     *          "foci": {
     *              "Reisen"
     *          },
     *          "production_status": "Flugbereit",
     *          "type": "Erkundung",
     *          "description": "[...]",
     *          "size": "Fahrzeug",
     *          "manufacturer": {
     *              "code": "TMBL",
     *              "name": "Tumbril"
     *          }
     *      }
     * })
     * @Response(404, body={"message": "No Ground Vehicle found for Query: Ground Vehicle Name", "status_code": 404})
     */
    public function show(string $groundVehicle)
    {
        $groundVehicle = urldecode($groundVehicle);

        try {
            $groundVehicle = GroundVehicle::where('name', $groundVehicle)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            $this->response->errorNotFound(sprintf(static::NOT_FOUND_STRING, $groundVehicle));
        }

        return $this->getResponse($groundVehicle);
    }

    /**
     * @return \Dingo\Api\Http\Response
     *
     * @Get("/")
     *
     * @Versions({"v1"})
     *
     * @Parameters({
     *      @Parameter("page", description="The page of results to view.", type="integer", default=1)
     *      @Parameter("limit", description="The Result limit. 0 = Limit disabled", type="integer", default=5)
     *      @Parameter("locale", description="The Locale Code to return. Valid values: de_DE, en_EN. Fallback language is en_EN", type="string")
     * })
     *
     * @Response(200, body={
     *     "data": {
     *          {
     *              "id": 7,
     *              "chassis_id": 2,
     *              "name": "Cyclone",
     *              "sizes": {
     *                  "length": 23,
     *                  "beam": 15.5,
     *                  "height": 7
     *              },
     *              "mass": 65925,
     *              "cargo_capacity": 2,
     *              "crew": {
     *                  "min": 1,
     *                  "max": 1
     *              },
     *              "speed": {
     *                  "scm": 275
     *              },
     *              "foci": {
     *                  {
     *                      "de_DE": "Reisen",
     *                      "en_EN": "Touring"
     *                  }
     *              },
     *              "production_status": {
     *                  "de_DE": "Flugbereit",
     *                  "en_EN": "flight-ready"
     *              },
     *              "type": {
     *                  "de_DE": "Erkundung",
     *                  "en_EN": "exploration"
     *              },
     *              "description": {
     *                  "de_DE": "[...]",
     *                  "en_EN": "[...]"
     *              },
     *              "size": {
     *                  "de_DE": "Fahrzeug",
     *                  "en_EN": "vehicle"
     *              },
     *              "manufacturer": {
     *                  "code": "TMBL",
     *                  "name": "Tumbril"
     *              }
     *          },
     *          {}
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
     */
    public function index()
    {
        return $this->getResponse(GroundVehicle::query());
    }

    /**
     * Search Endpoint
     *
     * @return \Dingo\Api\Http\Response
     */
    public function search()
    {
        $query = $this->request->get('query');
        $query = urldecode($query);
        $queryBuilder = GroundVehicle::where('name', 'like', "%{$query}%");

        if ($queryBuilder->count() === 0) {
            $this->response->errorNotFound(sprintf(static::NOT_FOUND_STRING, $query));
        }

        return $this->getResponse($queryBuilder);
    }
}
