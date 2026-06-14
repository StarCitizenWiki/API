<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\StarCitizen\Starmap;

use App\Attributes\CacheTag;
use App\Http\Controllers\Api\Concerns\ComputesFacets;
use App\Http\Controllers\Controller;
use App\Http\Includes\CustomEagerLoadInclude;
use App\Http\Includes\IncludeDefinition;
use App\Http\Requests\Api\Game\SearchRequest;
use App\Http\Resources\StarCitizen\Starmap\StarsystemResource;
use App\Models\StarCitizen\Starmap\Starsystem;
use App\Support\Filters\FilterCache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

#[CacheTag('starmap')]
class StarsystemController extends Controller
{
    use ComputesFacets;

    /**
     * @return array<int, AllowedFilter>
     */
    private function allowedFilters(): array
    {
        return [
            AllowedFilter::exact('affiliation', 'affiliation.name'),
            AllowedFilter::exact('code'),
            AllowedFilter::callback('name', static function (Builder $query, mixed $value): void {
                if (! is_string($value) || $value === '') {
                    return;
                }

                $query->whereLike('starmap_starsystems.name', "%{$value}%");
            }),
            AllowedFilter::exact('status'),
            AllowedFilter::exact('type'),
            AllowedFilter::exact('size', 'aggregated_size'),
        ];
    }

    /**
     * Build base query with filters and sorts for starsystems.
     */
    /**
     * @return array<int, IncludeDefinition>
     */
    private function includeDefinitions(): array
    {
        return [
            IncludeDefinition::relationship('affiliation'),
            IncludeDefinition::relationship('celestialObjects'),
            IncludeDefinition::custom('jumppoints', new CustomEagerLoadInclude(['jumppoints.entry', 'jumppoints.exit'])),
        ];
    }

    private function buildBaseQuery(Request $request): QueryBuilder
    {
        return QueryBuilder::for(Starsystem::class, $request)
            ->allowedIncludes(...IncludeDefinition::toSpatieIncludes($this->includeDefinitions()))
            ->allowedFilters(...$this->allowedFilters())
            ->allowedSorts(
                'name',
                'code',
                'status',
                'type',
                'aggregated_size',
                'aggregated_population',
                'aggregated_economy',
                'aggregated_danger',
            );
    }

    #[OA\Get(
        path: '/api/starsystems',
        operationId: 'listStarsystems',
        description: 'Returns paginated starsystems, optionally including related resources.',
        summary: 'Starmap Starsystems Overview',
        tags: ['Starmap'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/page_number'),
            new OA\Parameter(ref: '#/components/parameters/page_size'),
            new OA\Parameter(name: 'filter[affiliation]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[code]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[name]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[status]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[type]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[size]', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'sort', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(
                name: 'include',
                description: 'Include additional relationships (affiliation, celestialObjects, jumppoints).',
                in: 'query',
                schema: new OA\Schema(type: 'string'),
                explode: false,
                allowReserved: true,
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Paginated list of Starsystems',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/starsystem')),
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
        $collection = $this->buildBaseQuery($request)
            ->jsonPaginate()
            ->appends(request()->query());

        return StarsystemResource::collection($collection)
            ->additional(['meta' => ['valid_relations' => IncludeDefinition::toNames($this->includeDefinitions())]]);
    }

    #[OA\Get(
        path: '/api/starsystems/{code}',
        operationId: 'getStarsystem',
        description: 'Retrieve a starsystem by code or identifier, with optional includes.',
        summary: 'Starsystem Detail',
        tags: ['Starmap'],
        parameters: [
            new OA\Parameter(
                name: 'code',
                in: 'path',
                required: true,
                schema: new OA\Schema(
                    description: 'Starsystem code or identifier (e.g. SOL)',
                    type: 'string',
                ),
            ),
            new OA\Parameter(
                name: 'include',
                description: 'Include additional relationships (affiliation, celestialObjects, jumppoints).',
                in: 'query',
                schema: new OA\Schema(type: 'string'),
                explode: false,
                allowReserved: true,
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'A Starsystem',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/starsystem'),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: 404,
                description: 'No Starsystem with specified code found.',
                content: new OA\JsonContent(ref: '#/components/schemas/not_found_error_response'),
            ),
        ]
    )]
    public function show(Request $request): StarsystemResource
    {
        ['code' => $code] = Validator::validate(
            [
                'code' => $request->code,
            ],
            [
                'code' => 'required|string|min:1|max:255',
            ]
        );

        $code = mb_strtoupper(urldecode($code));

        $starsystem = QueryBuilder::for(Starsystem::class, $request)
            ->where('code', $code)
            ->orWhere('name', 'LIKE', "%$code%")
            ->allowedIncludes(...IncludeDefinition::toSpatieIncludes($this->includeDefinitions()))
            ->firstOrFail();

        return new StarsystemResource($starsystem)
            ->setValidIncludes(IncludeDefinition::toNames($this->includeDefinitions()));
    }

    #[OA\Post(
        path: '/api/starsystems/search',
        operationId: 'searchStarsystemsDeprecated',
        description: 'Deprecated. Use GET /api/starsystems?filter[name]={value} for name search. This endpoint will be removed in a future version.',
        summary: 'Starsystem Search (Deprecated)',
        requestBody: new OA\RequestBody(
            description: 'Partial starsystem code or name to search for',
            required: true,
            content: [
                new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(
                        properties: [
                            new OA\Property(property: 'query', type: 'string'),
                        ],
                        type: 'object',
                    ),
                    example: '{"query": "Sol"}',
                ),
            ],
        ),
        tags: ['Starmap', 'Search'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/page_number'),
            new OA\Parameter(ref: '#/components/parameters/page_size'),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of matching Starsystems',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/starsystem')),
                    ],
                    type: 'object'
                )
            ),
        ],
        deprecated: true
    )]
    public function search(SearchRequest $request): AnonymousResourceCollection|JsonResponse
    {
        $query = mb_strtoupper($request->validated('query'));

        $builder = $this->buildBaseQuery($request);

        $builder->where(function (Builder $b) use ($query) {
            $b->where('code', $query)
                ->orWhere('name', 'LIKE', "%$query%");

            if (is_numeric($query)) {
                $b->orWhere('cig_id', (int) $query);
            }
        });

        $starsystems = $builder
            ->jsonPaginate()
            ->appends(request()->query());

        return StarsystemResource::collection($starsystems)->additional([
            'meta' => ['deprecated' => true],
        ])->response()->header('Deprecated', 'true');
    }

    #[OA\Get(
        path: '/api/starsystems/filters',
        operationId: 'listStarsystemFilters',
        description: 'Return all available filter values for starsystems.',
        summary: 'Starsystem Filters',
        tags: ['Starmap'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Available filters for starsystems.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'filters',
                            properties: [
                                new OA\Property(property: 'affiliation', type: 'array', items: new OA\Items(ref: '#/components/schemas/filter_value')),
                                new OA\Property(property: 'status', type: 'array', items: new OA\Items(ref: '#/components/schemas/filter_value')),
                                new OA\Property(property: 'type', type: 'array', items: new OA\Items(ref: '#/components/schemas/filter_value')),
                                new OA\Property(property: 'size', type: 'array', items: new OA\Items(ref: '#/components/schemas/filter_value')),
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
        return Starsystem::class;
    }

    protected function facetDefinitions(Request $request): array
    {
        return [
            'affiliation' => [
                'expr' => 'starmap_affiliations.name',
                'join' => static fn ($q) => $q
                    ->leftJoin('starmap_starsystem_affiliation', 'starmap_starsystems.id', '=', 'starmap_starsystem_affiliation.starsystem_id')
                    ->leftJoin('starmap_affiliations', 'starmap_starsystem_affiliation.affiliation_id', '=', 'starmap_affiliations.id'),
            ],
            'status' => [
                'expr' => 'starmap_starsystems.status',
            ],
            'type' => [
                'expr' => 'starmap_starsystems.type',
            ],
            'size' => [
                'expr' => 'starmap_starsystems.aggregated_size',
                'cast' => static fn ($value) => $value === null ? null : (float) $value,
            ],
        ];
    }

    protected function facetCacheNamespace(): string
    {
        return FilterCache::NAMESPACE_STARSYSTEMS;
    }

    protected function facetCacheKey(Request $request): string
    {
        return FilterCache::starsystemsKey();
    }
}
