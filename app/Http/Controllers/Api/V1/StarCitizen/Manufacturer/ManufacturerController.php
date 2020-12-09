<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\StarCitizen\Manufacturer;

use App\Http\Controllers\Api\AbstractApiController as ApiController;
use App\Http\Requests\StarCitizen\Manufacturer\ManufacturerSearchRequest;
use App\Models\Api\StarCitizen\Manufacturer\Manufacturer;
use App\Transformers\Api\V1\StarCitizen\Manufacturer\ManufacturerTransformer;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Manufacturer API
 * Manufacturers found in the ShipMatrix
 *
 * @Resource("Manufacturers", uri="/manufacturers")
 */
class ManufacturerController extends ApiController
{
    /**
     * ManufacturerController constructor.
     *
     * @param Request                 $request
     * @param ManufacturerTransformer $transformer
     */
    public function __construct(Request $request, ManufacturerTransformer $transformer)
    {
        $this->transformer = $transformer;
        parent::__construct($request);
    }

    /**
     * Returns all manufacturers
     *
     * @Get("/{?page,limit,include,locale}")
     * @Versions({"v1"})
     * @Parameters({
     *     @Parameter("page", type="integer", required=false, description="Pagination page", default=1),
     *     @Parameter(
     *          "include",
     *          type="string",
     *          required=false,
     *          description="Relations to include. Valid relations are listed in the meta data"
     *     ),
     *     @Parameter(
     *          "limit",
     *          type="integer",
     *          required=false,
     *          description="Items per page, set to 0, to return all items",
     *          default=10
     *     ),
     *     @Parameter(
     *          "locale",
     *          type="string",
     *          required=false,
     *          description="Localization to use. Supported codes: 'de_DE', 'en_EN'"
     *     ),
     * })
     * @Request(headers={"Accept": "application/x.StarCitizenWikiApi.v1+json"})
     * @Response(200, body={
     * "data": {
     *  {
     *      "code": "RSI",
     *      "name": "Roberts Space Industries",
     *      "known_for": {
     *          "de_DE": "Die Aurora und die Constellation",
     *          "en_EN": "the Aurora and the Constellation"
     *      },
     *      "description": {
     *          "de_DE": "...",
     *          "en_EN": "..."
     *      },
     *  },
     *  {
     *      "code": "ORIG",
     *      "name": "Origin Jumpworks GmbH",
     *      "known_for": {
     *          "de_DE": "Die 300i Serie",
     *          "en_EN": "the 300i series"
     *      },
     *      "description": {
     *          "de_DE": "...",
     *          "en_EN": "..."
     *      },
     *  },
     * },
     * "meta": {
     *  "processed_at": "2020-12-07 13:25:54",
     *  "valid_relations": {
     *      "ships",
     *      "vehicles"
     *  },
     *  "pagination": {
     *      "total": 17,
     *      "count": 10,
     *      "per_page": 10,
     *      "current_page": 1,
     *      "total_pages": 2,
     *      "links": {
     *          "next": "https:\/\/api.star-citizen.wiki\/api\/manufacturers?page=2"
     *      }
     *  }
     * }
     * })
     *
     * @return Response
     */
    public function index(): Response
    {
        return $this->getResponse(Manufacturer::query());
    }

    /**
     * Returns a single manufacturer
     *
     * @Get("/{CODE}{?include,locale}")
     * @Versions({"v1"})
     * @Parameters({
     *     @Parameter("CODE", type="string", required=true, description="Manufacturer Code"),
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
     * @Request(headers={"Accept": "application/x.StarCitizenWikiApi.v1+json"}),
     * @Response(200, body={
     * "data": {
     *  {
     *      "code": "RSI",
     *      "name": "Roberts Space Industries",
     *      "known_for": {
     *          "de_DE": "Die Aurora und die Constellation",
     *          "en_EN": "the Aurora and the Constellation"
     *      },
     *      "description": {
     *          "de_DE": "...",
     *          "en_EN": "..."
     *      },
     *  }
     * },
     * "meta": {
     *  "processed_at": "2020-12-07 13:25:54",
     *  "valid_relations": {
     *      "ships",
     *      "vehicles"
     *  },
     * }
     * }),
     *
     * @Request({"locale": "de_DE"}, headers={"Accept": "application/x.StarCitizenWikiApi.v1+json"}),
     * @Response(200, body={
     * "data": {
     *  {
     *      "code": "RSI",
     *      "name": "Roberts Space Industries",
     *      "known_for": "Die Aurora und die Constellation",
     *      "description": "...",
     *  }
     * },
     * "meta": {
     *  "processed_at": "2020-12-07 13:25:54",
     *  "valid_relations": {
     *      "ships",
     *      "vehicles"
     *  },
     * }
     * }),
     *
     * @Request({"include": "ships"}, headers={"Accept": "application/x.StarCitizenWikiApi.v1+json"}),
     * @Response(200, body={
     * "data": {
     *  {
     *      "code": "RSI",
     *      "name": "Roberts Space Industries",
     *      "known_for": {
     *          "de_DE": "Die Aurora und die Constellation",
     *          "en_EN": "the Aurora and the Constellation"
     *      },
     *      "description": {
     *          "de_DE": "...",
     *          "en_EN": "..."
     *      },
     *      "ships": {
     *          "data": {
     *              {
     *                  "name": "Orion",
     *                  "slug": "orion",
     *                  "api_url": "https:\/\/api.star-citizen.wiki\/api\/ships\/orion"
     *              },
     *              {
     *                  "name": "Polaris",
     *                  "slug": "polaris",
     *                  "api_url": "https:\/\/api.star-citizen.wiki\/api\/ships\/polaris"
     *              },
     *              {
     *                  "name": "...",
     *              },
     *          }
     *      }
     *  }
     * },
     * "meta": {
     *  "processed_at": "2020-12-07 13:25:54",
     *  "valid_relations": {
     *      "ships",
     *      "vehicles"
     *  },
     * }
     * }),
     *
     * @Request({"include": "ships,vehicles"}, headers={"Accept": "application/x.StarCitizenWikiApi.v1+json"}),
     * @Response(200, body={
     * "data": {
     *  {
     *      "code": "RSI",
     *      "name": "Roberts Space Industries",
     *      "known_for": {
     *          "de_DE": "Die Aurora und die Constellation",
     *          "en_EN": "the Aurora and the Constellation"
     *      },
     *      "description": {
     *          "de_DE": "...",
     *          "en_EN": "..."
     *      },
     *      "ships": {
     *          "data": {
     *              {
     *                  "name": "Orion",
     *                  "slug": "orion",
     *                  "api_url": "https:\/\/api.star-citizen.wiki\/api\/ships\/orion"
     *              },
     *              {
     *                  "name": "Polaris",
     *                  "slug": "polaris",
     *                  "api_url": "https:\/\/api.star-citizen.wiki\/api\/ships\/polaris"
     *              },
     *              {
     *                  "name": "...",
     *              },
     *          }
     *      },
     *      "vehicles": {
     *          "data": {
     *              {
     *                  "name": "Ursa Rover",
     *                  "slug": "ursa-rover",
     *                  "api_url": "https:\/\/api.star-citizen.wiki\/api\/vehicles\/ursa-rover"
     *              },
     *              {
     *                  "name": "...",
     *              },
     *          }
     *      }
     *  }
     * },
     * "meta": {
     *  "processed_at": "2020-12-07 13:25:54",
     *  "valid_relations": {
     *      "ships",
     *      "vehicles"
     *  },
     * }
     * }),
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
                'manufacturer' => 'required|string|min:1|max:255',
            ]
        );

        $manufacturer = urldecode($request->get('manufacturer'));

        try {
            $model = Manufacturer::query()
                ->where('name_short', $manufacturer)
                ->orWhere('name', $manufacturer)
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            $this->response->errorNotFound(sprintf(static::NOT_FOUND_STRING, $manufacturer));
        }

        return $this->getResponse($model);
    }

    /**
     * Search Endpoint
     *
     * @Post("/search")
     * @Versions({"v1"})
     * @Parameters({
     *     @Parameter("query", type="string", required=true, description="Manufacturer Code or partial name"),
     *     @Parameter(
     *          "include",
     *          type="string",
     *          required=false,
     *          description="Relations to include. Valid relations are listed in the meta data"
     *     ),
     * })
     *
     * @Transaction({
     *      @Request({"query": "RSI"}, headers={"Accept": "application/x.StarCitizenWikiApi.v1+json"}),
     *      @Response(200, body={
     *      "data": {
     *       {
     *           "code": "RSI",
     *           "name": "Roberts Space Industries",
     *           "known_for": {
     *               "de_DE": "Die Aurora und die Constellation",
     *               "en_EN": "the Aurora and the Constellation"
     *           },
     *           "description": {
     *               "de_DE": "...",
     *               "en_EN": "..."
     *           },
     *       }
     *      },
     *      "meta": {
     *       "processed_at": "2020-12-07 13:25:54",
     *       "valid_relations": {
     *           "ships",
     *           "vehicles"
     *       },
     *      }
     *      }),
     *
     *      @Request({"query": "INVALID"}, headers={"Accept": "application/x.StarCitizenWikiApi.v1+json"}),
     *      @Response(404, body={"message": "No Results for Query 'INVALID'", "status_code": 404})
     * })
     *
     *
     * @param Request $request
     *
     * @return Response
     */
    public function search(Request $request): Response
    {
        $rules = (new ManufacturerSearchRequest())->rules();
        $request->validate($rules);

        $query = urldecode($request->get('query'));
        $queryBuilder = Manufacturer::query()
            ->where('name_short', 'like', "%{$query}%")
            ->orWhere('name', 'like', "%{$query}%");

        if ($queryBuilder->count() === 0) {
            $this->response->errorNotFound(sprintf(static::NOT_FOUND_STRING, $query));
        }

        return $this->getResponse($queryBuilder);
    }
}
