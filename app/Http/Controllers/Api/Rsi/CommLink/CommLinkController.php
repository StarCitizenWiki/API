<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Rsi\CommLink;

use App\Http\Controllers\Controller;
use App\Http\Resources\AbstractBaseResource;
use App\Http\Resources\Rsi\CommLink\CommLinkResource;
use App\Models\Rsi\CommLink\CommLink;
use App\Support\Filters\FilterCache;
use App\Support\Filters\FilterValues;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[OA\Parameter(
    parameter: 'comm_link_includes',
    name: 'include',
    in: 'query',
    schema: new OA\Schema(
        description: 'Available Comm-Link includes',
        type: 'array',
        items: new OA\Items(
            type: 'string',
            enum: [
                'translations',
                'images',
                'links',
            ]
        ),
    ),
    explode: false,
    allowReserved: true
)]
class CommLinkController extends Controller
{
    #[OA\Get(
        path: '/api/comm-links',
        description: 'Returns paginated comm-links with optional includes, categories, series, and channel filters.',
        summary: 'Comm-Links Overview',
        tags: ['Comm-Links', 'RSI-Website'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/comm_link_includes'),
            new OA\Parameter(name: 'filter[channel]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[series]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[category]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'sort', in: 'query', schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of Comm-Links',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/comm_link')
                )
            ),
        ]
    )]
    public function index(): AnonymousResourceCollection
    {
        $query = QueryBuilder::for(CommLink::class)
            ->allowedIncludes(CommLinkResource::validIncludes())
            ->allowedFilters([
                AllowedFilter::exact('category', 'category.name'),
                AllowedFilter::exact('series', 'series.name'),
                AllowedFilter::exact('channel', 'channel.name'),
            ])
            ->allowedSorts(['cig_id', 'created_at'])
            ->orderByDesc('cig_id')
            ->paginate()
            ->appends(request()->query());

        return CommLinkResource::collection($query);
    }

    #[OA\Get(
        path: '/api/comm-links/filters',
        description: 'Return all available filter values for Comm-Links.',
        summary: 'Comm-Link Filters',
        tags: ['Comm-Links', 'RSI-Website'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Available filters for Comm-Links.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'filters',
                            properties: [
                                new OA\Property(property: 'category', type: 'array', items: new OA\Items(ref: '#/components/schemas/filter_value')),
                                new OA\Property(property: 'channel', type: 'array', items: new OA\Items(ref: '#/components/schemas/filter_value')),
                                new OA\Property(property: 'series', type: 'array', items: new OA\Items(ref: '#/components/schemas/filter_value')),
                            ],
                            type: 'object'
                        ),
                    ],
                    type: 'object'
                )
            ),
        ]
    )]
    public function filters(Request $request): JsonResponse
    {
        $isAuthenticated = $request->user() !== null;

        $filters = FilterCache::rememberForever(
            FilterCache::NAMESPACE_COMM_LINKS,
            FilterCache::commLinksKey($isAuthenticated),
            static function (): array {
                $baseQuery = (new CommLink)->newQueryWithoutRelationships()->toBase();

                $categoryRows = (clone $baseQuery)
                    ->leftJoin('comm_link_categories', 'comm_links.category_id', '=', 'comm_link_categories.id')
                    ->selectRaw('comm_link_categories.name as value, count(*) as count')
                    ->groupBy('comm_link_categories.name')
                    ->orderByRaw('comm_link_categories.name IS NULL, comm_link_categories.name')
                    ->get();

                $channelRows = (clone $baseQuery)
                    ->leftJoin('comm_link_channels', 'comm_links.channel_id', '=', 'comm_link_channels.id')
                    ->selectRaw('comm_link_channels.name as value, count(*) as count')
                    ->groupBy('comm_link_channels.name')
                    ->orderByRaw('comm_link_channels.name IS NULL, comm_link_channels.name')
                    ->get();

                $seriesRows = (clone $baseQuery)
                    ->leftJoin('comm_link_series', 'comm_links.series_id', '=', 'comm_link_series.id')
                    ->selectRaw('comm_link_series.name as value, count(*) as count')
                    ->groupBy('comm_link_series.name')
                    ->orderByRaw('comm_link_series.name IS NULL, comm_link_series.name')
                    ->get();

                return [
                    'category' => FilterValues::fromRows($categoryRows),
                    'channel' => FilterValues::fromRows($channelRows),
                    'series' => FilterValues::fromRows($seriesRows),
                ];
            }
        );

        return response()->json([
            'filters' => $filters,
        ]);
    }

    #[OA\Get(
        path: '/api/comm-links/{id}',
        description: 'Retrieve a single comm-link by ID with the requested related resources.',
        summary: 'Comm-Link Detail',
        tags: ['Comm-Links', 'RSI-Website'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/comm_link_includes'),
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(
                    description: 'Comm-Link ID, starting from 12663',
                    type: 'integer',
                    format: 'int64',
                    minimum: 12663
                ),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'A singular Comm-Link',
                content: new OA\JsonContent(ref: '#/components/schemas/comm_link')
            ),
            new OA\Response(
                response: 404,
                description: 'No Comm-Link with specified ID found.',
            ),
        ]
    )]
    public function show(Request $request): AbstractBaseResource
    {
        ['comm_link' => $commLink] = Validator::validate(
            [
                'comm_link' => $request->id,
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
