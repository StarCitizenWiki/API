<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Rsi\Transcript;

use App\Http\Controllers\Api\AbstractApiController as ApiController;
use App\Models\Transcript\Transcript;
use App\Transformers\Api\V1\Rsi\Transcript\TranscriptTransformer;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Comm-Link API
 * Scraped Comm-Links from Roberts Space Industries
 *
 * @Resource("Transcripts", uri="/transcripts")
 */
class TranscriptController extends ApiController
{
    /**
     * CommLinkController constructor.
     *
     * @param Request $request
     * @param TranscriptTransformer $transformer
     */
    public function __construct(Request $request, TranscriptTransformer $transformer)
    {
        $this->transformer = $transformer;

        // Don't include translation per default
        $this->transformer->setDefaultIncludes(array_slice($this->transformer->getAvailableIncludes(), 0, 2));

        parent::__construct($request);
    }

    /**
     * Returns all Transcripts
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
     *      {
     *          "title": "Inside Star Citizen: Report Purport | Summer 2021",
     *          "youtube_id": "9gYBBb_FsCE",
     *          "youtube_url": "https://www.youtube.com/watch?v=9gYBBb_FsCE",
     *          "playlist_name": "Inside Star Citizen",
     *          "upload_date": "2021-09-23",
     *          "runtime": "1041",
     *          "runtime_formatted": "00:17:21",
     *          "thumbnail": "https://i.ytimg.com/vi/9gYBBb_FsCE/maxresdefault.jpg",
     *          "description": "YouTube Description",
     *      },
     *      {
     *          "title": "...",
     *      }
     * },
     * "meta": {
     *      "processed_at": "2020-12-07 14:45:18",
     *      "valid_relations": {
     *          "english",
     *          "german"
     *      },
     *      "pagination": {
     *          "total": 1550,
     *          "count": 15,
     *          "per_page": 15,
     *          "current_page": 1,
     *          "total_pages": 104,
     *          "links": {
     *          "next": "https:\/\/api.star-citizen.wiki\/api\/transcripts?page=2"
     *      }
     * }
     * }
     * })
     *
     * @return Response
     */
    public function index(): Response
    {
        return $this->getResponse(Transcript::query()->orderByDesc('upload_date'));
    }

    /**
     * Returns a singular transcript by its youtube-id
     *
     * @Get("/{ID}{?include}")
     * @Versions({"v1"})
     * @Parameters({
     *     @Parameter("ID", type="string", required=true, description="YouTube Video ID"),
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
     * @Response(200, body={
     * "data": {
     *      "title": "Inside Star Citizen: Report Purport | Summer 2021",
     *      "youtube_id": "9gYBBb_FsCE",
     *      "youtube_url": "https://www.youtube.com/watch?v=9gYBBb_FsCE",
     *      "playlist_name": "Inside Star Citizen",
     *      "upload_date": "2021-09-23",
     *      "runtime": "1041",
     *      "runtime_formatted": "00:17:21",
     *      "thumbnail": "https://i.ytimg.com/vi/9gYBBb_FsCE/maxresdefault.jpg",
     *      "description": "YouTube Description",
     * },
     * "meta": {
     *      "processed_at": "2020-12-07 14:52:11",
     *      "valid_relations": {
     *          "english",
     *          "german"
     *      },
     *      "prev_id": "zKdN8N44d1g",
     *      "next_id": null
     * }
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
        ['transcripts' => $transcript] = Validator::validate(
            [
                'transcripts' => $request->transcript,
            ],
            [
                'transcripts' => 'required|string|min:11|max:20',
            ]
        );

        try {
            $transcript = Transcript::query()->where('youtube_id', $transcript)->firstOrFail();
            $transcript->append(['prev', 'next']);
        } catch (ModelNotFoundException $e) {
            return new Response(['code' => 404, 'message' => sprintf(static::NOT_FOUND_STRING, $transcript)], 404);
        }

        $this->extraMeta = [
            'prev_id' => optional($transcript->prev)->youtube_id ?? null,
            'prev_title' => optional($transcript->prev)->title ?? null,
            'next_id' => optional($transcript->next)->youtube_id ?? null,
            'next_title' => optional($transcript->next)->title ?? null,
        ];

        return $this->getResponse($transcript);
    }
}
