<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V2\StarCitizen;

use App\Http\Controllers\Api\V2\AbstractApiV2Controller;
use App\Http\Requests\StarCitizen\Galactapedia\GalactapediaSearchRequest;
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

#[OA\Parameter(
    name: 'galactapedia_includes_v2',
    in: 'query',
    schema: new OA\Schema(
        schema: 'include',
        description: 'Available Galactapedia includes',
        collectionFormat: 'csv',
        enum: [
            'translations',
            'tags',
            'categories',
            'related_articles',
            'properties',
        ]
    ),
    allowReserved: true
)]
class GalactapediaController extends AbstractApiV2Controller
{
    #[OA\Get(
        path: '/api/v2/galactapedia',
        tags: ['Galactapedia', 'RSI-Website'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/limit'),
            new OA\Parameter(ref: '#/components/parameters/galactapedia_includes_v2'),
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
                    items: new OA\Items(ref: '#/components/schemas/galactapedia_article_v2')
                )
            )
        ]
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = QueryBuilder::for(Article::class, $request)
            ->allowedIncludes(ArticleResource::validIncludes())
            ->allowedFilters([
                AllowedFilter::exact('category', 'category.name'),
                AllowedFilter::exact('categoryId', 'category.cig_id'),

                AllowedFilter::exact('tag', 'tag.name'),
                AllowedFilter::exact('tagId', 'tag.cig_id'),

                AllowedFilter::exact('property', 'property.name'),
                AllowedFilter::exact('template', 'template.template'),
            ])
            ->orderByDesc('id')
            ->paginate($this->limit)
            ->appends(request()->query());

        return ArticleResource::collection($query);
    }

    #[OA\Get(
        path: '/api/v2/galactapedia/{id}',
        tags: ['Galactapedia', 'RSI-Website'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/locale'),
            new OA\Parameter(ref: '#/components/parameters/galactapedia_includes_v2'),
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(
                    schema: 'galactapedia_id_v2',
                    description: 'Galactapedia Article ID',
                    type: 'string',
                ),
            ),
        ],
        responses: [
            new OA\Response(
                ref: '#/components/schemas/galactapedia_article_v2',
                response: 200,
                description: 'A singular Article',
            ),
            new OA\Response(
                response: 404,
                description: 'No Article with specified ID found.',
            )
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
        path: '/api/v2/galactapedia/search',
        requestBody: new OA\RequestBody(
            description: 'Article (partial) title, template or slug',
            required: true,
            content: [
                new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(
                        schema: 'query',
                        type: 'json',
                    ),
                    example: '{"query": "Banu"}',
                )
            ]
        ),
        tags: ['Galactapedia', 'RSI-Website'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of articles matching the query',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/galactapedia_article_v2')
                )
            ),
            new OA\Response(
                response: 404,
                description: 'No Article found.',
            )
        ],
    )]
    public function search(Request $request): AnonymousResourceCollection
    {
        $rules = (new GalactapediaSearchRequest())->rules();
        $request->validate($rules);

        $query = $this->cleanQueryName($request->get('query'));

        $queryBuilder = QueryBuilder::for(Article::class, $request)
            ->where('title', 'like', "%{$query}%")
            ->orWhere('slug', 'like', "%{$query}%")
            ->orWhere('cig_id', 'like', "%{$query}%")
            ->orWhereHas('templates', function (Builder $builder) use ($query) {
                return $builder->where('template', 'like', "%{$query}%");
            });

        if ($queryBuilder->count() === 0) {
            throw new NotFoundHttpException('No Article(s) for specified query found.');
        }

        return ArticleResource::collection($queryBuilder->get());
    }
}
