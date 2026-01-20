<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\StarCitizen;

use App\Http\Controllers\Controller;
use App\Http\Filters\DateFilter;
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
use OpenApi\Attributes as OA;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class GalactapediaController extends Controller
{
    /**
     * Build base query with filters, sorts, and counts for articles.
     */
    private function buildBaseQuery(Request $request): QueryBuilder
    {
        return QueryBuilder::for(Article::class, $request)
            ->allowedFilters([
                AllowedFilter::scope('category'),
                AllowedFilter::scope('tag'),
                AllowedFilter::scope('template'),
                AllowedFilter::partial('title'),
                AllowedFilter::custom('created_at', new DateFilter('created_at')),
            ])
            ->allowedSorts([
                'title',
                'categories_count',
                'tags_count',
                'related_articles_count',
            ])
            ->defaultSort('-id')
            ->with(['categories', 'tags', 'templates']);
    }

    #[OA\Get(
        path: '/api/galactapedia',
        description: 'Return paginated Galactapedia articles with category, tag, and template filters.',
        summary: 'Galactapedia Overview',
        tags: ['Galactapedia', 'RSI-Website'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/page_number'),
            new OA\Parameter(ref: '#/components/parameters/page_size'),
            new OA\Parameter(name: 'filter[category]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[tag]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[template]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[title]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[created_at]', in: 'query', schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of Galactapedia Articles',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/galactapedia_article')
                )
            ),
        ]
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = $this->buildBaseQuery($request)
            ->jsonPaginate()
            ->appends(request()->query());

        return ArticleResource::collection($query);
    }

    #[OA\Get(
        path: '/api/galactapedia/filters',
        description: 'Return all available filter values for Galactapedia articles.',
        summary: 'Galactapedia Filters',
        tags: ['Galactapedia', 'RSI-Website'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Available filters for Galactapedia.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'filters',
                            properties: [
                                new OA\Property(property: 'category', type: 'array', items: new OA\Items(ref: '#/components/schemas/filter_value')),
                                new OA\Property(property: 'tag', type: 'array', items: new OA\Items(ref: '#/components/schemas/filter_value')),
                                new OA\Property(property: 'template', type: 'array', items: new OA\Items(ref: '#/components/schemas/filter_value')),
                            ],
                            type: 'object'
                        ),
                    ],
                    type: 'object'
                )
            ),
        ]
    )]
    public function filters(): JsonResponse
    {
        $filters = FilterCache::rememberForever(
            FilterCache::NAMESPACE_GALACTAPEDIA,
            FilterCache::galactapediaKey(),
            static function (): array {
                $baseQuery = (new Article)->newQueryWithoutRelationships()->toBase();

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
            }
        );

        return response()->json([
            'filters' => $filters,
        ]);
    }

    #[OA\Get(
        path: '/api/galactapedia/{id}',
        description: 'Retrieve a Galactapedia article by ID with available includes and translations.',
        summary: 'Galactapedia Article',
        tags: ['Galactapedia', 'RSI-Website'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/locale'),
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(
                    description: 'Galactapedia Article ID',
                    type: 'string',
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
                            'translations',
                            'tags',
                            'categories',
                            'related_articles',
                            'properties',
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
                content: new OA\JsonContent(ref: '#/components/schemas/galactapedia_article')
            ),
            new OA\Response(
                response: 404,
                description: 'No Article with specified ID found.',
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
                ->with(ArticleResource::validIncludes())
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            throw new NotFoundHttpException('No Article with specified ID found.');
        }

        return new ArticleResource($model);
    }

    #[OA\Post(
        path: '/api/galactapedia/search',
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
        tags: ['Galactapedia', 'RSI-Website', 'Search'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of articles matching the query',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/galactapedia_article')
                )
            ),
        ],
        deprecated: true,
    )]
    public function search(SearchRequest $request): AnonymousResourceCollection|\Illuminate\Http\JsonResponse
    {
        $query = $request->validated('query');

        $queryBuilder = QueryBuilder::for(Article::class, $request)
            ->where('title', 'ilike', "%{$query}%")
            ->orWhere('slug', 'like', "%{$query}%")
            ->orWhere('cig_id', $query)
            ->orWhereHas('templates', function (Builder $builder) use ($query) {
                return $builder->where('template', 'like', "%{$query}%");
            })
            ->jsonPaginate()
            ->appends(request()->query());

        return ArticleResource::collection($queryBuilder)->additional([
            'meta' => ['deprecated' => true],
        ])->response()->header('Deprecated', 'true');
    }
}
