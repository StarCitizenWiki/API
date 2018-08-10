<?php declare(strict_types = 1);

namespace App\Http\Controllers\Api\V1\StarCitizen\Vehicle\Ship;

use App\Http\Controllers\Api\AbstractApiController as ApiController;
use App\Models\Api\StarCitizen\Vehicle\Ship\Ship;
use App\Transformers\Api\V1\StarCitizen\Vehicle\Ship\ShipTransformer;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

/**
 * @Resource("Ships", uri="/vehicles/ships")
 *
 * @covers \App\Http\Controllers\Api\AbstractApiController
 */
class ShipController extends ApiController
{
    /**
     * {@inheritdoc}
     */
    protected const NOT_FOUND_STRING = 'No Ship found for Query: %s';

    /**
     * ShipController constructor.
     *
     * @param \App\Transformers\Api\V1\StarCitizen\Vehicle\Ship\ShipTransformer $transformer
     * @param \Illuminate\Http\Request                                          $request
     */
    public function __construct(ShipTransformer $transformer, Request $request)
    {
        $this->transformer = $transformer;
        parent::__construct($request);
    }

    /**
     * @param string $ship
     *
     * @return \Dingo\Api\Http\Response
     *
     * @GET("/{shipName}")
     *
     * @Versions({"v1"})
     *
     * @Parameters({
     *      @Parameter("locale", description="The Translation to return.")
     * })
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
     *              "de_DE": "Klein",
     *              "en_EN": "small"
     *          },
     *          "manufacturer": {
     *              "code": "ORIG",
     *              "name": "Origin Jumpworks GmbH"
     *          }
     *      }
     * })
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
     *          "foci": {
     *              "Reisen"
     *          },
     *          "production_status": "Flugbereit",
     *          "type": "Erkundung",
     *          "description": "[...]",
     *          "size": "Klein",
     *          "manufacturer": {
     *              "code": "ORIG",
     *              "name": "Origin Jumpworks GmbH"
     *          }
     *      }
     * })
     * @Response(404, body={"message": "No Ship found for Query: Ship Name", "status_code": 404})
     */
    public function show(string $ship)
    {
        $ship = urldecode($ship);

        try {
            $ship = Ship::where('name', $ship)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            $this->response->errorNotFound(sprintf(static::NOT_FOUND_STRING, $ship));
        }

        return $this->getResponse($ship);
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
     * })
     *
     * @Response(200, body={
     *     "data": {
     *          {
     *              "id": 7,
     *              "chassis_id": 2,
     *              "name": "300i",
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
     *                  "scm": 275,
     *                  "afterburner": 1190
     *              },
     *              "rotation": {
     *                  "pitch": 85,
     *                  "yaw": 85,
     *                  "roll": 120
     *              },
     *              "acceleration": {
     *                  "x_axis": 68,
     *                  "y_axis": 80.3,
     *                  "z_axis": 71.7
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
     *                  "en_EN": "If you're going to travel the stars... [...]"
     *              },
     *              "size": {
     *                  "de_DE": "Klein",
     *                  "en_EN": "small"
     *              },
     *              "manufacturer": {
     *                  "code": "ORIG",
     *                  "name": "Origin Jumpworks GmbH"
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
        return $this->getResponse(Ship::query());
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
        $queryBuilder = Ship::where('name', 'like', "%{$query}%");

        if ($queryBuilder->count() === 0) {
            $this->response->errorNotFound(sprintf(static::NOT_FOUND_STRING, $query));
        }

        return $this->getResponse($queryBuilder);
    }
}
