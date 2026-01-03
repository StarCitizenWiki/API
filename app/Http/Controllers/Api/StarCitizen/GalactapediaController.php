<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\StarCitizen;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Game\SearchRequest;
use App\Http\Resources\AbstractBaseResource;
use App\Http\Resources\StarCitizen\Galactapedia\ArticleResource;
use App\Models\StarCitizen\Galactapedia\Article;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class GalactapediaController extends Controller
{
    #[OA\Get(
        path: '/api/galactapedia',
        description: 'Return paginated Galactapedia articles with category, tag, property, and template filters.',
        summary: 'Galactapedia Overview',
        tags: ['Galactapedia', 'RSI-Website'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(name: 'filter[category]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[categoryId]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[tag]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[tagId]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[property]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[template]', in: 'query', schema: new OA\Schema(type: 'string')),
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
        $query = QueryBuilder::for(Article::class, $request)
            ->allowedFilters([
                AllowedFilter::exact('category', 'category.name'),
                AllowedFilter::exact('categoryId', 'category.cig_id'),

                AllowedFilter::exact('tag', 'tag.name'),
                AllowedFilter::exact('tagId', 'tag.cig_id'),

                AllowedFilter::exact('property', 'property.name'),
                AllowedFilter::exact('template', 'template.template'),
            ])
            ->orderByDesc('id')
            ->paginate()
            ->appends(request()->query());

        return ArticleResource::collection($query);
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
        description: 'Search Galactapedia articles by title, template, slug, or related metadata.',
        summary: 'Galactapedia Article Search',
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
            new OA\Response(
                response: 404,
                description: 'No Article found.',
            ),
        ],
    )]
    public function search(SearchRequest $request): AnonymousResourceCollection
    {
        $query = $request->validated('query');

        $queryBuilder = QueryBuilder::for(Article::class, $request)
            ->where('title', 'like', "%{$query}%")
            ->orWhere('slug', 'like', "%{$query}%")
            ->orWhere('cig_id', $query)
            ->orWhereHas('templates', function (Builder $builder) use ($query) {
                return $builder->where('template', 'like', "%{$query}%");
            })
            ->paginate()
            ->appends(request()->query());

        return ArticleResource::collection($queryBuilder);
    }
}
