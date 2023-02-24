<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Rsi\CommLink\Channel;

use App\Http\Controllers\Api\AbstractApiController as ApiController;
use App\Models\Rsi\CommLink\Channel\Channel;
use App\Transformers\Api\V1\Rsi\CommLink\Channel\ChannelTransformer;
use App\Transformers\Api\V1\Rsi\CommLink\CommLinkTransformer;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use OpenApi\Attributes as OA;

/**
 * Class Channel Controller
 */
class ChannelController extends ApiController
{
    /**
     * StatsAPIController constructor.
     *
     * @param Request            $request
     * @param ChannelTransformer $transformer
     */
    public function __construct(Request $request, ChannelTransformer $transformer)
    {
        $this->transformer = $transformer;
        parent::__construct($request);
    }

    #[OA\Get(
        path: '/api/comm-links/channels',
        tags: ['Comm-Links', 'RSI-Website'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/limit'),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of Channels',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/comm_link_channel')
                )
            )
        ]
    )]
    public function index(): Response
    {
        $categories = Channel::query()->orderBy('name');

        return $this->getResponse($categories);
    }

    #[OA\Get(
        path: '/api/comm-links/channels/{channel}',
        tags: ['Comm-Links', 'RSI-Website'],
        parameters: [
            new OA\Parameter(
                name: 'category',
                description: 'Name or slug of the category',
                in: 'path',
                required: true,
            ),
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/limit'),
        ],
        responses: [
            new OA\Response(
                ref: '#/components/schemas/comm_link_channel',
                response: 200,
                description: 'A singular Comm-Link Channel',
            ),
            new OA\Response(
                response: 404,
                description: 'No Channel with specified name found.',
            )
        ]
    )]
    public function show(string $channel): Response
    {
        try {
            $channel = Channel::query()
                ->where('name', $channel)
                ->orWhere('slug', $channel)
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            $this->response->errorNotFound(sprintf(static::NOT_FOUND_STRING, $channel));
        }

        $this->transformer = new CommLinkTransformer();

        return $this->getResponse($channel->commLinks()->orderByDesc('cig_id'));
    }
}
