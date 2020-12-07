<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Rsi\CommLink;

use App\Http\Controllers\Api\AbstractApiController as ApiController;
use App\Models\Rsi\CommLink\CommLink;
use App\Transformers\Api\V1\Rsi\CommLink\CommLinkTransformer;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Comm-Link API
 * Scraped Comm-Links from Roberts Space Industries
 *
 * @Resource("Comm-Links", uri="/comm-links")
 */
class CommLinkController extends ApiController
{
    /**
     * CommLinkController constructor.
     *
     * @param Request             $request
     * @param CommLinkTransformer $transformer
     */
    public function __construct(Request $request, CommLinkTransformer $transformer)
    {
        $this->transformer = $transformer;
        parent::__construct($request);
    }

    /**
     * Returns all Comm-Links
     *
     * @Get("/{?page,limit,include}")
     * @Versions({"v1"})
     * @Parameters({
     *     @Parameter("page", type="integer", required=false, description="Pagination page", default=1),
     *     @Parameter(
     *          "include",
     *          type="string",
     *          required=false,
     *          description="Relations to include. Valid relations are shown in the meta data"
     *     ),
     *     @Parameter(
     *          "limit",
     *          type="integer",
     *          required=false,
     *          description="Items per page, set to 0, to return all items",
     *          default=10
     *     ),
     * })
     * @Request(headers={"Accept": "application/x.StarCitizenWikiApi.v1+json"})
     * @Response(200, body={
     * "data": {
     *  {
     *      "id": 17911,
     *      "title": "Star Citizen Live",
     *      "rsi_url": "https:\/\/robertsspaceindustries.com\/comm-link\/transmission\/17911-Star-Citizen-Live",
     *      "api_url": "https:\/\/api.star-citizen.wiki\/api\/comm-links\/17911",
     *      "api_public_url": "https:\/\/api.star-citizen.wiki\/comm-links\/17911",
     *      "channel": "Transmission",
     *      "category": "General",
     *      "series": "Star Citizen LIVE",
     *      "images": 1,
     *      "links": 2,
     *      "comment_count": 4,
     *      "created_at": "2020-12-03T23:00:00.000000Z"
     *  },
     *  {
     *      "id": 17909,
     *      "title": "Inside Star Citizen",
     *      "rsi_url": "https:\/\/robertsspaceindustries.com\/comm-link\/transmission\/17909-Inside-Star-Citizen",
     *      "api_url": "https:\/\/api.star-citizen.wiki\/api\/comm-links\/17909",
     *      "api_public_url": "https:\/\/api.star-citizen.wiki\/comm-links\/17909",
     *      "channel": "Transmission",
     *      "category": "General",
     *      "series": "Inside Star Citizen",
     *      "images": 1,
     *      "links": 2,
     *      "comment_count": 18,
     *      "created_at": "2020-12-02T23:00:00.000000Z"
     *  },
     * },
     * "meta": {
     *      "processed_at": "2020-12-07 14:45:18",
     *      "valid_relations": {
     *          "images",
     *          "links",
     *          "english",
     *          "german"
     *      },
     *      "pagination": {
     *          "total": 4229,
     *          "count": 15,
     *          "per_page": 15,
     *          "current_page": 1,
     *          "total_pages": 282,
     *          "links": {
     *          "next": "https:\/\/api.star-citizen.wiki\/api\/comm-links?page=2"
     *      }
     * }
     * })
     *
     * @return Response
     */
    public function index(): Response
    {
        $commLinks = CommLink::query()->orderByDesc('cig_id');

        return $this->getResponse($commLinks);
    }

    /**
     * Returns a singular comm-link by its cig_id
     *
     * @Get("/{ID}{?include}")
     * @Versions({"v1"})
     * @Parameters({
     *     @Parameter("ID", type="interger", required=true, description="Comm-Link ID"),
     *     @Parameter(
     *          "include",
     *          type="string",
     *          required=false,
     *          description="Relations to include. Valid relations are shown in the meta data"
     *     ),
     * })
     * @Request(headers={"Accept": "application/x.StarCitizenWikiApi.v1+json"})
     * @Response(200, body={
     * "data": {
     *      "id": 17911,
     *      "title": "Star Citizen Live",
     *      "rsi_url": "https:\/\/robertsspaceindustries.com\/comm-link\/transmission\/17911-Star-Citizen-Live",
     *      "api_url": "https:\/\/api.star-citizen.wiki\/api\/comm-links\/17911",
     *      "api_public_url": "https:\/\/api.star-citizen.wiki\/comm-links\/17911",
     *      "channel": "Transmission",
     *      "category": "General",
     *      "series": "Star Citizen LIVE",
     *      "images": {
     *          "data": {
     *              {
     *                  "rsi_url": "...",
     *                  "api_url": null,
     *                  "alt": "",
     *                  "size": 18693,
     *                  "mime_type": "image\/png",
     *                  "last_modified": "2016-05-05T01:15:45.000000Z"
     *              }
     *          }
     *      },
     *      "links": {
     *          "data": {
     *              {
     *                  "href": "http:\/\/twitch.tv\/starcitizen",
     *                  "text": "http:\/\/twitch.tv\/starcitizen"
     *              },
     *              {
     *                  "href": "https:\/\/www.youtube.com\/embed\/gsWDdomcMCM?wmode=transparent",
     *                  "text": "iframe"
     *              }
     *          }
     *      },
     *      "comment_count": 4,
     *      "created_at": "2020-12-03T23:00:00.000000Z"
     * },
     * "meta": {
     *      "processed_at": "2020-12-07 14:52:11",
     *      "valid_relations": {
     *          "images",
     *          "links",
     *          "english",
     *          "german"
     *      },
     *      "prev_id": 17909,
     *      "next_id": -1
     * }
     * })
     *
     * @param int $commLink
     *
     * @return Response
     */
    public function show(int $commLink): Response
    {
        try {
            $commLink = CommLink::query()->where('cig_id', $commLink)->firstOrFail();
            $commLink->append(['prev', 'next']);
        } catch (ModelNotFoundException $e) {
            $this->response->errorNotFound(sprintf(static::NOT_FOUND_STRING, $commLink));
        }

        // Don't include translation per default
        $this->transformer->setDefaultIncludes(array_slice($this->transformer->getAvailableIncludes(), 0, 2));

        $this->extraMeta = [
            'prev_id' => optional($commLink->prev)->cig_id ?? -1,
            'next_id' => optional($commLink->next)->cig_id ?? -1,
        ];

        return $this->getResponse($commLink);
    }
}
