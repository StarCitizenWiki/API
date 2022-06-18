<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Rsi\CommLink;

use App\Http\Controllers\Api\AbstractApiController as ApiController;
use App\Models\Rsi\CommLink\CommLink;
use App\Transformers\Api\V1\Rsi\CommLink\CommLinkTransformer;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;

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

        // Don't include translation per default
        $this->transformer->setDefaultIncludes(array_slice($this->transformer->getAvailableIncludes(), 0, 2));

        parent::__construct($request);
    }

    #[OA\Get(
        path: '/api/comm-links',
        tags: ['Comm-Links', 'RSI-Website'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/limit'),
            new OA\Parameter(
                name: 'include',
                in: 'query',
                schema: new OA\Schema(
                    schema: 'comm_link_includes',
                    description: 'Available Comm-Link includes',
                    collectionFormat: 'csv',
                    enum: [
                        'english',
                        'german',
                        'images',
                        'links',
                    ]
                ),
                allowReserved: true
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of Comm-Links',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/comm_link')
                )
            )
        ]
    )]
    public function index(): Response
    {
        return $this->getResponse(CommLink::query()->orderByDesc('cig_id'));
    }

    #[OA\Get(
        path: '/api/comm-links/{id}',
        tags: ['Comm-Links', 'RSI-Website'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/limit'),
            new OA\Parameter(
                name: 'include',
                in: 'query',
                schema: new OA\Schema(
                    schema: 'comm_link_includes',
                    description: 'Available Comm-Link includes',
                    collectionFormat: 'csv',
                    enum: [
                        'english',
                        'german',
                        'images',
                        'links',
                    ]
                ),
                allowReserved: true
            ),
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(
                    schema: 'comm_link_id',
                    description: 'Comm-Link ID, starting from 12663',
                    type: 'integer',
                    format: 'int64',
                    minimum: 12663
                ),
            ),
        ],
        responses: [
            new OA\Response(
                ref: '#/components/schemas/comm_link',
                response: 200,
                description: 'A singular Comm-Link',
            ),
            new OA\Response(
                response: 404,
                description: 'No Comm-Link with specified ID found.',
            )
        ]
    )]
    public function show(Request $request): Response
    {
        ['comm_link' => $commLink] = Validator::validate(
            [
                'comm_link' => $request->comm_link,
            ],
            [
                'comm_link' => 'required|int|min:12663',
            ]
        );

        try {
            $commLink = CommLink::query()->where('cig_id', $commLink)->firstOrFail();
            $commLink->append(['prev', 'next']);
        } catch (ModelNotFoundException $e) {
            $this->response->errorNotFound(sprintf(static::NOT_FOUND_STRING, $commLink));
        }

        $this->extraMeta = [
            'prev_id' => optional($commLink->prev)->cig_id ?? -1,
            'next_id' => optional($commLink->next)->cig_id ?? -1,
        ];

        return $this->getResponse($commLink);
    }
}
