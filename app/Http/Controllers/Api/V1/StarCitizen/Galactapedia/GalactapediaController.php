<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\StarCitizen\Galactapedia;

use App\Http\Controllers\Api\AbstractApiController as ApiController;
use App\Http\Requests\StarCitizen\Galactapedia\GalactapediaSearchRequest;
use App\Models\StarCitizen\Galactapedia\Article;
use App\Transformers\Api\V1\StarCitizen\Galactapedia\ArticleTransformer;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Galactapedia article API
 *
 * @Resource("Galactapedia", uri="/galactapedia")
 */
class GalactapediaController extends ApiController
{
    /**
     * ManufacturerController constructor.
     *
     * @param Request $request
     * @param ArticleTransformer $transformer
     */
    public function __construct(Request $request, ArticleTransformer $transformer)
    {
        $this->transformer = $transformer;
        parent::__construct($request);
    }

    /**
     * Returns all galactapedia articles
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
     *   {
     *     "id": "0rxrqgDP12",
     *     "title": "Gammon Messer",
     *     "slug": "gammon-messer",
     *     "thumbnail": "...",
     *     "type": "People",
     *     "url": "https:\/\/robertsspaceindustries.com\/galactapedia\/article\/0rxrqgDP12-gammon-messer",
     *     "api_url": "https:\/\/api.star-citizen.wiki\/api\/alactapedia\/0rxrqgDP12"
     *   },
     *   {
     *     "id": "0KxqnXDpQ2",
     *     "title": "Empire's Light Conversion Centers",
     *     "slug": "empires-light-conversion-centers",
     *     "thumbnail": "...",
     *     "type": "PlanetMoonSpaceStationPlatform",
     *     "url": "...",
     *     "api_url": "https:\/\/api.star-citizen.wiki\/api\/galactapedia\/0KxqnXDpQ2"
     *   },
     * },
     * "meta": {
     *  "processed_at": "2020-12-07 13:25:54",
     *  "valid_relations": {
     *     "categories",
     *     "properties",
     *     "tags",
     *     "related_articles",
     *     "english"
     *  },
     *  "pagination": {
     *      "total": 17,
     *      "count": 10,
     *      "per_page": 10,
     *      "current_page": 1,
     *      "total_pages": 2,
     *      "links": {
     *          "next": "https:\/\/api.star-citizen.wiki\/api\/galactapedia?page=2"
     *      }
     *  }
     * }
     * })
     *
     * @return Response
     */
    public function index(): Response
    {
        return $this->getResponse(Article::query()->orderByDesc('id'));
    }

    /**
     * Returns a single galactapedia article
     *
     * @Get("/{ID}{?include,locale}")
     * @Versions({"v1"})
     * @Parameters({
     *     @Parameter("ID", type="string", required=true, description="Galactapedia Article ID"),
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
     *   "id": "0rxrqgDP12",
     *   "title": "Gammon Messer",
     *   "slug": "gammon-messer",
     *   "thumbnail": "https:\/\/cig-galactapedia-prod.s3.amazonaws.com\/upload\/1b0d3793-0d81-4fe9-8d31-9483088e30a8",
     *   "type": "People",
     *   "url": "https:\/\/robertsspaceindustries.com\/galactapedia\/article\/0rxrqgDP12-gammon-messer",
     *   "api_url": "https:\/\/api.star-citizen.wiki\/api\/galactapedia\/0rxrqgDP12",
     *   "categories": {
     *      "data": {
     *       {
     *         "id": "R6vNW9BjPa",
     *         "name": "People"
     *       },
     *       {
     *         "id": "R6vqrLdp2e",
     *         "name": "Human"
     *       }
     *     }
     *   },
     *   "properties": {
     *     "data": {
     *       {
     *         "name": "classification",
     *         "value": "Human"
     *       },
     *       {
     *         "name": "affiliation",
     *         "value": "United Empire of Earth"
     *       },
     *       {
     *         "name": "born",
     *         "value": "2629"
     *       },
     *       {
     *         "name": "died",
     *         "value": "2662"
     *       }
     *     }
     *   },
     *   "tags": {
     *     "data": {
     *       {
     *        "id": "bo1gxKa8xw",
     *        "name": "gammon messer"
     *       },
     *       {
     *         "id": "R56nykvABN",
     *         "name": "messer dynasty"
     *       },
     *       {
     *         "id": "VaZwQy6PX5",
     *         "name": "messer era"
     *       }
     *     }
     *   },
     *   "related_articles": {
     *     "data": {
     *       {
     *         "id": "0KnA1D5nQM",
     *         "title": "Astrid Messer VII",
     *         "url": "https:\/\/robertsspaceindustries.com\/galactapedia\/article\/0KnA1D5nQM-astrid-messer-vii",
     *         "api_url": "https:\/\/api.star-citizen.wiki\/api\/galactapedia\/0KnA1D5nQM"
     *       },
     *       {
     *         "id": "0OaB8O6awO",
     *         "title": "Corsen Messer V",
     *         "url": "https:\/\/robertsspaceindustries.com\/galactapedia\/article\/0OaB8O6awO-corsen-messer-v",
     *         "api_url": "https:\/\/api.star-citizen.wiki\/api\/galactapedia\/0OaB8O6awO"
     *       },
     *       {
     *         "id": "bZwQBNmWrL",
     *         "title": "Illyana Messer VI",
     *         "url": "https:\/\/robertsspaceindustries.com\/galactapedia\/article\/bZwQBNmWrL-illyana-messer-vi",
     *         "api_url": "https:\/\/api.star-citizen.wiki\/api\/galactapedia\/bZwQBNmWrL"
     *       }
     *     }
     *   },
     *   "english": {
     *     "data": {
     *       "locale": "en_EN",
     *       "translation": "..."
     *     }
     *   },
     *   "meta": {
     *     "processed_at": "2021-02-08 10:45:28",
     *     "valid_relations": {
     *       "categories",
     *       "properties",
     *       "tags",
     *       "related_articles",
     *       "english"
     *     }
     *   }
     * }
     * }),
     *
     * })
     *
     * @param Request $request
     *
     * @return Response
     * @throws ValidationException
     */
    public function show(Request $request): Response
    {
        ['article' => $article] = Validator::validate(
            [
                'article' => $request->article,
            ],
            [
                'article' => 'required|string|min:10|max:12',
            ]
        );

        $article = urldecode($article);

        try {
            $model = Article::query()
                ->where('cig_id', $article)
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            $this->response->errorNotFound(sprintf(static::NOT_FOUND_STRING, $article));
        }

        $this->transformer->includeAllAvailableIncludes();

        return $this->getResponse($model);
    }

    /**
     * Search Endpoint
     *
     * @Post("/search")
     * @Versions({"v1"})
     * @Parameters({
     *     @Parameter("query", type="string", required=true, description="Article (partial) title or slug"),
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
     *        "Like show": ""
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
        $rules = (new GalactapediaSearchRequest())->rules();
        $request->validate($rules);

        $query = urldecode($request->get('query'));
        $queryBuilder = Article::query()
            ->where('title', 'like', "%{$query}%")
            ->orWhere('slug', 'like', "%{$query}%");

        if ($queryBuilder->count() === 0) {
            $this->response->errorNotFound(sprintf(static::NOT_FOUND_STRING, $query));
        }

        return $this->getResponse($queryBuilder);
    }
}
