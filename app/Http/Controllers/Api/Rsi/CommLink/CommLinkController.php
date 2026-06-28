<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Rsi\CommLink;

use App\Attributes\CacheTag;
use App\Http\Controllers\Api\Concerns\ComputesFacets;
use App\Http\Controllers\Controller;
use App\Http\Filters\DateFilter;
use App\Http\Filters\SortByRelation;
use App\Http\Includes\IncludeDefinition;
use App\Http\Resources\AbstractBaseResource;
use App\Http\Resources\Rsi\CommLink\CommLinkResource;
use App\Models\Rsi\CommLink\CommLink;
use App\Support\Filters\FilterCache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
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
                'images',
                'links',
            ]
        ),
    ),
    explode: false,
    allowReserved: true
)]
#[CacheTag('comm-links')]
class CommLinkController extends Controller
{
    use ComputesFacets;

    /**
     * @return array<int, IncludeDefinition>
     */
    private function includeDefinitions(): array
    {
        return [
            IncludeDefinition::relationship('images'),
            IncludeDefinition::relationship('links'),
        ];
    }

    /**
     * @return array<int, AllowedFilter>
     */
    private function allowedFilters(): array
    {
        return [
            AllowedFilter::exact('id', 'cig_id'),
            AllowedFilter::callback('title', static function (Builder $query, mixed $value): void {
                if (! is_string($value) || $value === '') {
                    return;
                }

                $query->whereLike('comm_links.title', "%{$value}%");
            }),
            AllowedFilter::callback('content', static function (Builder $query, mixed $value): void {
                if (! is_string($value)) {
                    return;
                }

                $searchTerm = trim($value);

                if ($searchTerm === '') {
                    return;
                }

                $query->whereFullText('translation->en', $searchTerm, [
                    'language' => 'english',
                    'mode' => 'websearch',
                ]);
            }),
            AllowedFilter::exact('channel', 'channel.name'),
            AllowedFilter::exact('category', 'category.name'),
            AllowedFilter::exact('series', 'series.name'),
            AllowedFilter::custom('created_at', new DateFilter('comm_links.created_at')),
        ];
    }

    private function buildBaseQuery(Request $request): QueryBuilder
    {
        return QueryBuilder::for(CommLink::class, $request)
            ->with(['channel', 'category', 'series'])
            ->allowedIncludes(...IncludeDefinition::toSpatieIncludes($this->includeDefinitions()))
            ->allowedFilters(...$this->allowedFilters())
            ->allowedSorts(AllowedSort::field('id', 'cig_id'), 'title', 'images_count', 'links_count', AllowedSort::custom('channel', new SortByRelation, 'channel.name'), AllowedSort::custom('category', new SortByRelation, 'category.name'), AllowedSort::custom('series', new SortByRelation, 'series.name'), 'created_at');
    }

    #[OA\Get(
        path: '/api/comm-links',
        operationId: 'listCommLinks',
        description: 'Returns paginated comm-links ordered by descending ID by default. Supports filtering by channel, category, series, title, content, and publication date. Results can be sorted by id, title, images_count, links_count, channel, category, series, and created_at. Use the include parameter to embed images or links.',
        summary: 'Comm-Links Overview',
        tags: ['Comm-Links'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/page_number'),
            new OA\Parameter(ref: '#/components/parameters/page_size'),
            new OA\Parameter(ref: '#/components/parameters/comm_link_includes'),
            new OA\Parameter(name: 'filter[id]', description: 'Exact match on the Comm-Link CIG ID', in: 'query', schema: new OA\Schema(type: 'integer', example: 12663)),
            new OA\Parameter(name: 'filter[title]', description: 'Partial match on the Comm-Link title', in: 'query', schema: new OA\Schema(type: 'string', example: 'This Week in Star Citizen')),
            new OA\Parameter(name: 'filter[content]', description: 'Full-text search within English Comm-Link translations', in: 'query', schema: new OA\Schema(type: 'string', example: 'star citizen')),
            new OA\Parameter(name: 'filter[channel]', description: 'Exact match on channel name (see GET /api/comm-links/filters for valid values)', in: 'query', schema: new OA\Schema(type: 'string', example: 'Engineering')),
            new OA\Parameter(name: 'filter[series]', description: 'Exact match on series name (see GET /api/comm-links/filters for valid values)', in: 'query', schema: new OA\Schema(type: 'string', example: 'Around the Verse')),
            new OA\Parameter(name: 'filter[category]', description: 'Exact match on category name (see GET /api/comm-links/filters for valid values)', in: 'query', schema: new OA\Schema(type: 'string', example: 'General')),
            new OA\Parameter(
                name: 'filter[created_at]',
                description: 'Filter by publication year (YYYY) or exact date (YYYY-MM-DD)',
                in: 'query',
                schema: new OA\Schema(type: 'string', example: '2025')
            ),
            new OA\Parameter(
                name: 'sort',
                description: 'Sort field. Prefix with "-" for descending. Supported: id, title, images_count, links_count, channel, category, series, created_at.',
                in: 'query',
                schema: new OA\Schema(type: 'string', example: '-id')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Paginated list of Comm-Links',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/comm_link')),
                        new OA\Property(property: 'links', allOf: [new OA\Schema(ref: '#/components/schemas/pagination_links')]),
                        new OA\Property(property: 'meta', allOf: [new OA\Schema(ref: '#/components/schemas/pagination_meta')]),
                    ],
                    type: 'object'
                )
            ),
        ]
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = $this->buildBaseQuery($request)
            ->when(! $request->has('sort'), function ($query) {
                $query->orderByDesc('cig_id');
            })
            ->jsonPaginate()
            ->appends($request->query());

        return CommLinkResource::collection($query)
            ->additional(['meta' => ['valid_relations' => IncludeDefinition::toNames($this->includeDefinitions())]]);
    }

    #[OA\Get(
        path: '/api/comm-links/filters',
        operationId: 'listCommLinkFilters',
        description: 'Returns available category, channel, and series filter values for Comm-Links, with occurrence counts. Providing additional filter parameters will narrow the facets accordingly.',
        summary: 'Comm-Link Filters',
        tags: ['Comm-Links'],
        parameters: [
            new OA\Parameter(name: 'filter[id]', description: 'Exact match on the Comm-Link CIG ID', in: 'query', schema: new OA\Schema(type: 'integer', example: 12663)),
            new OA\Parameter(name: 'filter[title]', description: 'Partial match on the Comm-Link title', in: 'query', schema: new OA\Schema(type: 'string', example: 'This Week in Star Citizen')),
            new OA\Parameter(name: 'filter[content]', description: 'Full-text search within English Comm-Link translations', in: 'query', schema: new OA\Schema(type: 'string', example: 'star citizen')),
            new OA\Parameter(name: 'filter[channel]', description: 'Exact match on channel name', in: 'query', schema: new OA\Schema(type: 'string', example: 'Engineering')),
            new OA\Parameter(name: 'filter[series]', description: 'Exact match on series name', in: 'query', schema: new OA\Schema(type: 'string', example: 'Around the Verse')),
            new OA\Parameter(name: 'filter[category]', description: 'Exact match on category name', in: 'query', schema: new OA\Schema(type: 'string', example: 'General')),
            new OA\Parameter(name: 'filter[created_at]', description: 'Filter by publication year (YYYY) or exact date (YYYY-MM-DD)', in: 'query', schema: new OA\Schema(type: 'string', example: '2025')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Available filters for Comm-Links.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'filters',
                            properties: [
                                new OA\Property(property: 'category', description: 'Category names such as General, Community, Lore, Development', type: 'array', items: new OA\Items(ref: '#/components/schemas/filter_value')),
                                new OA\Property(property: 'channel', description: 'Channel names such as Engineering, Transmission, Featured post', type: 'array', items: new OA\Items(ref: '#/components/schemas/filter_value')),
                                new OA\Property(property: 'series', description: 'Series names such as Around the Verse, 10 For the Chairman', type: 'array', items: new OA\Items(ref: '#/components/schemas/filter_value')),
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
        return $this->computeFacetsResponse($request);
    }

    protected function facetModelClass(): string
    {
        return CommLink::class;
    }

    protected function facetDefinitions(Request $request): array
    {
        return [
            'category' => [
                'expr' => 'comm_link_categories.name',
                'join' => static fn ($q) => $q->leftJoin('comm_link_categories', 'comm_links.category_id', '=', 'comm_link_categories.id'),
            ],
            'channel' => [
                'expr' => 'comm_link_channels.name',
                'join' => static fn ($q) => $q->leftJoin('comm_link_channels', 'comm_links.channel_id', '=', 'comm_link_channels.id'),
            ],
            'series' => [
                'expr' => 'comm_link_series.name',
                'join' => static fn ($q) => $q->leftJoin('comm_link_series', 'comm_links.series_id', '=', 'comm_link_series.id'),
            ],
        ];
    }

    protected function facetCacheNamespace(): string
    {
        return FilterCache::NAMESPACE_COMM_LINKS;
    }

    protected function facetCacheKey(Request $request): string
    {
        return FilterCache::commLinksKey($request->user() !== null);
    }

    #[OA\Get(
        path: '/api/comm-links/{id}',
        operationId: 'getCommLink',
        description: 'Retrieve a single Comm-Link by its CIG ID. Images with hash and metadata are always included. The response contains prev_id and next_id metadata for sequential navigation between Comm-Links.',
        summary: 'Comm-Link Detail',
        tags: ['Comm-Links'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/comm_link_includes'),
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(
                    description: 'Comm-Link CIG ID, starting from 12663',
                    type: 'integer',
                    format: 'int64',
                    minimum: 12663,
                    example: 12663
                ),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'A singular Comm-Link',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/comm_link'),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: 404,
                description: 'No Comm-Link with specified ID found.',
                content: new OA\JsonContent(ref: '#/components/schemas/not_found_error_response'),
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
                ->with(['channel', 'category', 'series'])
                ->allowedIncludes(...IncludeDefinition::toSpatieIncludes($this->includeDefinitions()))
                ->with(['images.hash', 'images.metadata'])
                ->withNavigation()
                ->firstOrFail();
        } catch (ModelNotFoundException) {
            throw new NotFoundHttpException('No Comm-Link with specified ID found.');
        }

        $resource = (new CommLinkResource($commLink))
            ->setValidIncludes(IncludeDefinition::toNames($this->includeDefinitions()));
        $resource->addMetadata([
            'prev_id' => $commLink->prev_id ?? -1,
            'next_id' => $commLink->next_id ?? -1,
        ]);

        return $resource;
    }
}
