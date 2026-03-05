<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Rsi\CommLink;

use App\Http\Controllers\Controller;
use App\Http\Filters\DateFilter;
use App\Http\Filters\SortByRelation;
use App\Http\Resources\AbstractBaseResource;
use App\Http\Resources\Rsi\CommLink\CommLinkResource;
use App\Models\Rsi\CommLink\CommLink;
use App\Support\Filters\FilterCache;
use App\Support\Filters\FilterValues;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
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
            new OA\Parameter(ref: '#/components/parameters/page_number'),
            new OA\Parameter(ref: '#/components/parameters/page_size'),
            new OA\Parameter(ref: '#/components/parameters/comm_link_includes'),
            new OA\Parameter(name: 'filter[id]', description: 'Filter by comm-link ID', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[title]', description: 'Filter by partial comm-link title', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[channel]', description: 'Filter by channel name', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[series]', description: 'Filter by series name', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[category]', description: 'Filter by category name', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(
                name: 'filter[created_at]',
                description: 'Filter by publication year (YYYY) or date (YYYY-MM-DD).',
                in: 'query',
                schema: new OA\Schema(type: 'string')
            ),
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
                AllowedFilter::exact('id', 'cig_id'),
                AllowedFilter::partial('title'),
                AllowedFilter::exact('channel', 'channel.name'),
                AllowedFilter::exact('category', 'category.name'),
                AllowedFilter::exact('series', 'series.name'),
                AllowedFilter::custom('created_at', new DateFilter('created_at')),
            ])
            ->allowedSorts([
                AllowedSort::field('id', 'cig_id'),
                'title',
                'images_count',
                'links_count',
                AllowedSort::custom('channel', new SortByRelation, 'channel.name'),
                AllowedSort::custom('category', new SortByRelation, 'category.name'),
                AllowedSort::custom('series', new SortByRelation, 'series.name'),
                'created_at',
            ])
            ->when(! request()->has('sort'), function ($query) {
                $query->orderByDesc('cig_id');
            })
            ->jsonPaginate()
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

                $facets = [
                    'category' => [
                        'expr' => 'comm_link_categories.name',
                        'join' => static fn ($q) => $q->leftJoin('comm_link_categories', 'comm_links.category_id', '=', 'comm_link_categories.id'),
                        'cast' => null,
                    ],
                    'channel' => [
                        'expr' => 'comm_link_channels.name',
                        'join' => static fn ($q) => $q->leftJoin('comm_link_channels', 'comm_links.channel_id', '=', 'comm_link_channels.id'),
                        'cast' => null,
                    ],
                    'series' => [
                        'expr' => 'comm_link_series.name',
                        'join' => static fn ($q) => $q->leftJoin('comm_link_series', 'comm_links.series_id', '=', 'comm_link_series.id'),
                        'cast' => null,
                    ],
                ];

                $out = [];

                foreach ($facets as $key => $facet) {
                    $expr = $facet['expr'];

                    $q = clone $baseQuery;

                    if (isset($facet['join'])) {
                        ($facet['join'])($q);
                    }

                    $rows = $q
                        ->select([
                            DB::raw("{$expr} as value"),
                            DB::raw('count(*) as count'),
                        ])
                        ->groupByRaw($expr)
                        ->orderByRaw("{$expr} IS NULL, {$expr}")
                        ->get();

                    $out[$key] = FilterValues::fromRows($rows, $facet['cast'] ?? null);
                }

                return $out;
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
                ->with(['images.hash', 'images.metadata'])
                ->withNavigation()
                ->firstOrFail();
        } catch (ModelNotFoundException) {
            throw new NotFoundHttpException('No Comm-Link with specified ID found.');
        }

        $resource = new CommLinkResource($commLink);
        $resource->addMetadata([
            'prev_id' => $commLink->prev_id ?? -1,
            'next_id' => $commLink->next_id ?? -1,
        ]);

        return $resource;
    }
}
