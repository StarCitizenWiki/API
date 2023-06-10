<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Rsi\CommLink\Series;

use App\Http\Controllers\Api\AbstractApiController as ApiController;
use App\Models\Rsi\CommLink\Series\Series;
use App\Transformers\Api\V1\Rsi\CommLink\CommLinkTransformer;
use App\Transformers\Api\V1\Rsi\CommLink\Series\SeriesTransformer;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;

/**
 * Class Series Controller
 */
class SeriesController extends ApiController
{
    /**
     * StatsAPIController constructor.
     *
     * @param Request           $request
     * @param SeriesTransformer $transformer
     */
    public function __construct(Request $request, SeriesTransformer $transformer)
    {
        $this->transformer = $transformer;
        parent::__construct($request);
    }

    #[OA\Get(
        path: '/api/comm-links/series',
        tags: ['Comm-Links', 'RSI-Website'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/limit'),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of Series',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/comm_link_series')
                )
            )
        ]
    )]
    public function index(): Response
    {
        $categories = Series::query()->orderBy('name');

        return $this->getResponse($categories);
    }

    #[OA\Get(
        path: '/api/comm-links/series/{series}',
        tags: ['Comm-Links', 'RSI-Website'],
        parameters: [
            new OA\Parameter(
                name: 'category',
                description: 'Name or slug of the series',
                in: 'path',
                required: true,
            ),
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/limit'),
        ],
        responses: [
            new OA\Response(
                ref: '#/components/schemas/comm_link_series',
                response: 200,
                description: 'A singular Comm-Link Series',
            ),
            new OA\Response(
                response: 404,
                description: 'No Series with specified name found.',
            )
        ]
    )]
    public function show(string $series): Response
    {
        try {
            $series = Series::query()
                ->where('name', $series)
                ->orWhere('slug', $series)
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            return new Response(['code' => 404, 'message' => sprintf(static::NOT_FOUND_STRING, $series)], 404);
        }

        $this->transformer = new CommLinkTransformer();

        return $this->getResponse($series->commLinks()->orderByDesc('cig_id'));
    }
}
