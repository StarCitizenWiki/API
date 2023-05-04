<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V2\Rsi\CommLink;

use App\Http\Controllers\Api\V2\AbstractApiV2Controller;
use App\Http\Resources\AbstractBaseResource;
use App\Http\Resources\Rsi\CommLink\CommLinkResource;
use App\Models\Rsi\CommLink\CommLink;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CommLinkController extends AbstractApiV2Controller
{
    #[OA\Get(
        path: '/api/v2/comm-links',
        tags: ['Comm-Links', 'RSI-Website'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/limit'),
            new OA\Parameter(
                name: 'include',
                in: 'query',
                schema: new OA\Schema(
                    schema: 'comm_link_includes_v2',
                    description: 'Available Comm-Link includes',
                    collectionFormat: 'csv',
                    enum: [
                        'translations',
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
                    items: new OA\Items(ref: '#/components/schemas/comm_link_v2')
                )
            )
        ]
    )]
    public function index()
    {
        $query = QueryBuilder::for(CommLink::class)
            ->limit($this->limit)
            ->allowedIncludes(CommLinkResource::validIncludes())
            ->orderByDesc('cig_id')
            ->paginate()
            ->appends(request()->query());

        return CommLinkResource::collection($query);
    }

    #[OA\Get(
        path: '/api/v2/comm-links/{id}',
        tags: ['Comm-Links', 'RSI-Website'],
        parameters: [
            new OA\Parameter(ref: '#/components/schemas/comm_link_includes_v2'),
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(
                    schema: 'comm_link_id_v2',
                    description: 'Comm-Link ID, starting from 12663',
                    type: 'integer',
                    format: 'int64',
                    minimum: 12663
                ),
            ),
        ],
        responses: [
            new OA\Response(
                ref: '#/components/schemas/comm_link_v2',
                response: 200,
                description: 'A singular Comm-Link',
            ),
            new OA\Response(
                response: 404,
                description: 'No Comm-Link with specified ID found.',
            )
        ]
    )]
    public function show($id, Request $request): AbstractBaseResource
    {
        ['comm_link' => $commLink] = Validator::validate(
            [
                'comm_link' => $id,
            ],
            [
                'comm_link' => 'required|int|min:12663',
            ]
        );

        try {
            $commLink = QueryBuilder::for(CommLink::class)
                ->where('cig_id', $commLink)
                ->allowedIncludes(CommLinkResource::validIncludes())
                ->firstOrFail();
            $commLink->append(['prev', 'next']);
        } catch (ModelNotFoundException $e) {
            throw new NotFoundHttpException('No Comm-Link with specified ID found.');
        }

        $resource = new CommLinkResource($commLink);
        $resource->addMetadata([
            'prev_id' => optional($commLink->prev)->cig_id ?? -1,
            'next_id' => optional($commLink->next)->cig_id ?? -1,
        ]);

        return $resource;
    }
}
