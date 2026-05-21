<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\StarCitizen;

use App\Attributes\CacheTag;
use App\Http\Controllers\Controller;
use App\Http\Filters\DateFilter;
use App\Http\Includes\IncludeDefinition;
use App\Http\Requests\Api\Game\SearchRequest;
use App\Http\Resources\AbstractBaseResource;
use App\Http\Resources\StarCitizen\Galactapedia\ArticleResource;
use App\Models\StarCitizen\Galactapedia\Article;
use App\Support\Filters\FilterCache;
use App\Support\Filters\FilterValues;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use OpenApi\Attributes as OA;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[CacheTag('galactapedia')]
class GalactapediaController extends Controller
{
    /**
     * @return array<int, IncludeDefinition>
     */
    private function includeDefinitions(): array
    {
        return [
            IncludeDefinition::relationship('categories'),
            IncludeDefinition::relationship('properties'),
            IncludeDefinition::relationship('tags'),
            IncludeDefinition::relationship('related'),
        ];
    }

    /**
     * @return array<int, AllowedFilter>
     */
    private function allowedFilters(): array
    {
        return [
            AllowedFilter::scope('category'),
            AllowedFilter::scope('tag'),
            AllowedFilter::scope('template'),
            AllowedFilter::partial('title'),
            AllowedFilter::custom('created_at', new DateFilter('created_at')),
        ];
    }

    /**
     * Relationships required by list resources to avoid per-row lazy loads.
     *
     * @return array<int, string>
     */
    private function listResourceRelations(): array
    {
        return [
            'templates:id,template',
            'categories:id,name',
            'tags:id,name',
        ];
    }

    /**
     * Build base query with filters, sorts, and counts for articles.
     */
    private function buildBaseQuery(Request $request): QueryBuilder
    {
        return QueryBuilder::for(Article::class, $request)
            ->allowedFilters(...$this->allowedFilters())
            ->allowedSorts('title', 'categories_count', 'tags_count', 'related_articles_count'

            )
            ->defaultSort('-id');
    }

    #[OA\Get(
        path: '/api/galactapedia',
        operationId: 'listGalactapediaArticles',
        description: 'Returns paginated Galactapedia articles ordered by descending ID by default. Each article includes its templates, categories, and tags. Supports filtering by category, tag, template, title, and creation date. Results can be sorted by title, categories_count, tags_count, and related_articles_count.',
        summary: 'Galactapedia Overview',
        tags: ['Galactapedia'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/page_number'),
            new OA\Parameter(ref: '#/components/parameters/page_size'),
            new OA\Parameter(name: 'filter[category]', description: 'Exact match on category name (see GET /api/galactapedia/filters for valid values)', in: 'query', schema: new OA\Schema(type: 'string', example: 'Animals')),
            new OA\Parameter(name: 'filter[tag]', description: 'Exact match on tag name (see GET /api/galactapedia/filters for valid values)', in: 'query', schema: new OA\Schema(type: 'string', example: '100i')),
            new OA\Parameter(name: 'filter[template]', description: 'Exact match on template name (see GET /api/galactapedia/filters for valid values)', in: 'query', schema: new OA\Schema(type: 'string', example: 'Civilization')),
            new OA\Parameter(name: 'filter[title]', description: 'Partial match on the article title', in: 'query', schema: new OA\Schema(type: 'string', example: 'Messer')),
            new OA\Parameter(name: 'filter[created_at]', description: 'Filter by creation year (YYYY), year-month (YYYY-MM), or exact date (YYYY-MM-DD)', in: 'query', schema: new OA\Schema(type: 'string', example: '2025')),
            new OA\Parameter(
                name: 'sort',
                description: 'Sort field. Prefix with "-" for descending. Supported: title, categories_count, tags_count, related_articles_count.',
                in: 'query',
                schema: new OA\Schema(type: 'string', example: '-id')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Paginated list of Galactapedia Articles',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/galactapedia_article')),
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
            ->with($this->listResourceRelations())
            ->jsonPaginate()
            ->appends(request()->query());

        return ArticleResource::collection($query)
            ->additional(['meta' => ['valid_relations' => IncludeDefinition::toNames($this->includeDefinitions())]]);
    }

    #[OA\Get(
        path: '/api/galactapedia/filters',
        operationId: 'listGalactapediaFilters',
        description: 'Returns available category, tag, and template filter values for Galactapedia articles, with occurrence counts. Providing additional filter parameters will narrow the facets accordingly.',
        summary: 'Galactapedia Filters',
        tags: ['Galactapedia'],
        parameters: [
            new OA\Parameter(name: 'filter[category]', description: 'Exact match on category name', in: 'query', schema: new OA\Schema(type: 'string', example: 'Animals')),
            new OA\Parameter(name: 'filter[tag]', description: 'Exact match on tag name', in: 'query', schema: new OA\Schema(type: 'string', example: '100i')),
            new OA\Parameter(name: 'filter[template]', description: 'Exact match on template name', in: 'query', schema: new OA\Schema(type: 'string', example: 'Civilization')),
            new OA\Parameter(name: 'filter[title]', description: 'Partial match on the article title', in: 'query', schema: new OA\Schema(type: 'string', example: 'Messer')),
            new OA\Parameter(name: 'filter[created_at]', description: 'Filter by creation year (YYYY), year-month (YYYY-MM), or exact date (YYYY-MM-DD)', in: 'query', schema: new OA\Schema(type: 'string', example: '2025')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Available filters for Galactapedia.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'filters',
                            properties: [
                                new OA\Property(property: 'category', description: 'Category names such as Animals, Archaeology, Art, Banu, Civilizations', type: 'array', items: new OA\Items(ref: '#/components/schemas/filter_value')),
                                new OA\Property(property: 'tag', description: 'Tag names such as vehicle models, locations, and lore terms', type: 'array', items: new OA\Items(ref: '#/components/schemas/filter_value')),
                                new OA\Property(property: 'template', description: 'Template types such as Civilization, Company, Event', type: 'array', items: new OA\Items(ref: '#/components/schemas/filter_value')),
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
        $resolver = function () use ($request): array {
            $baseQuery = QueryBuilder::for(Article::class, $request)
                ->allowedFilters(...$this->allowedFilters());

            $facets = [
                'category' => [
                    'expr' => 'galactapedia_categories.name',
                    'join' => static fn ($q) => $q
                        ->leftJoin('galactapedia_article_categories', 'galactapedia_articles.id', '=', 'galactapedia_article_categories.article_id')
                        ->leftJoin('galactapedia_categories', 'galactapedia_article_categories.category_id', '=', 'galactapedia_categories.id'),
                    'cast' => null,
                ],
                'tag' => [
                    'expr' => 'galactapedia_tags.name',
                    'join' => static fn ($q) => $q
                        ->leftJoin('galactapedia_article_tags', 'galactapedia_articles.id', '=', 'galactapedia_article_tags.article_id')
                        ->leftJoin('galactapedia_tags', 'galactapedia_article_tags.tag_id', '=', 'galactapedia_tags.id'),
                    'cast' => null,
                ],
                'template' => [
                    'expr' => 'galactapedia_templates.template',
                    'join' => static fn ($q) => $q
                        ->leftJoin('galactapedia_article_templates', 'galactapedia_articles.id', '=', 'galactapedia_article_templates.article_id')
                        ->leftJoin('galactapedia_templates', 'galactapedia_article_templates.template_id', '=', 'galactapedia_templates.id'),
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
        };

        if (FilterCache::hasEffectiveFilters($request->input('filter', []))) {
            $filters = $resolver();
        } else {
            $filters = FilterCache::rememberForever(
                FilterCache::NAMESPACE_GALACTAPEDIA,
                FilterCache::galactapediaKey(),
                $resolver
            );
        }

        return response()->json([
            'filters' => $filters,
        ]);
    }

    #[OA\Get(
        path: '/api/galactapedia/{id}',
        operationId: 'getGalactapediaArticle',
        description: 'Retrieve a Galactapedia article by ID with available includes and translations.',
        summary: 'Galactapedia Article',
        tags: ['Galactapedia'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/locale'),
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(
                    description: 'Galactapedia Article CIG ID',
                    type: 'string',
                    example: 'VyvYAGKxAz',
                ),
            ),
            new OA\Parameter(
                name: 'include',
                in: 'query',
                schema: new OA\Schema(
                    description: 'Available Galactapedia includes',
                    type: 'array',
                    items: new OA\Items(
                        type: 'string',
                        enum: [
                            'categories',
                            'properties',
                            'tags',
                            'related',
                        ]
                    ),
                ),
                explode: false,
                allowReserved: true
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'A singular Article',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/galactapedia_article'),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: 404,
                description: 'No Article with specified ID found.',
                content: new OA\JsonContent(ref: '#/components/schemas/not_found_error_response'),
            ),
        ]
    )]
    public function show(Request $request): AbstractBaseResource
    {
        ['article' => $identifier] = Validator::validate(
            [
                'article' => $request->article,
            ],
            [
                'article' => 'required|string|min:10|max:12',
            ]
        );

        $identifier = $this->cleanQueryName($identifier);

        try {
            $model = QueryBuilder::for(Article::class, $request)
                ->where('cig_id', $identifier)
                ->allowedIncludes(...IncludeDefinition::toSpatieIncludes($this->includeDefinitions()))
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            throw new NotFoundHttpException('No Article with specified ID found.');
        }

        return (new ArticleResource($model))
            ->setValidIncludes(IncludeDefinition::toNames($this->includeDefinitions()));
    }

    #[OA\Post(
        path: '/api/galactapedia/search',
        operationId: 'searchGalactapediaDeprecated',
        description: 'Deprecated. Use GET /api/galactapedia?filter[title]={value} for title search. This endpoint will be removed in a future version.',
        summary: 'Galactapedia Article Search (Deprecated)',
        requestBody: new OA\RequestBody(
            description: 'Article (partial) title, template or slug',
            required: true,
            content: [
                new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(
                        type: 'object',
                    ),
                    example: '{"query": "Banu"}',
                ),
            ]
        ),
        tags: ['Galactapedia', 'Search'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of articles matching the query',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/galactapedia_article')),
                    ],
                    type: 'object'
                )
            ),
        ],
        deprecated: true,
    )]
    public function search(SearchRequest $request): AnonymousResourceCollection|JsonResponse
    {
        $query = $request->validated('query');

        $queryBuilder = QueryBuilder::for(Article::class, $request)
            ->where(function ($q) use ($query) {
                $q->whereLike('title', "%{$query}%")
                    ->orWhere('slug', 'like', "%{$query}%");

                if (is_numeric($query)) {
                    $q->orWhere('cig_id', (int) $query);
                }
            })
            ->when(Str::length($query) >= 3, function ($q) use ($query) {
                return $q->orWhereHas('templates', fn (Builder $builder) => $builder->where('template', 'like', "%{$query}%")
                );
            })
            ->with($this->listResourceRelations())
            ->jsonPaginate()
            ->appends(request()->query());

        return ArticleResource::collection($queryBuilder)->additional([
            'meta' => ['deprecated' => true],
        ])->response()->header('Deprecated', 'true');
    }
}
