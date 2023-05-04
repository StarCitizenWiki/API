<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V2\StarCitizen\Galactapedia;

use App\Http\Controllers\Api\V2\AbstractApiV2Controller;
use App\Http\Requests\StarCitizen\Galactapedia\GalactapediaSearchRequest;
use App\Http\Resources\AbstractBaseResource;
use App\Http\Resources\StarCitizen\Galactapedia\ArticleResource;
use App\Models\StarCitizen\Galactapedia\Article;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class GalactapediaController extends AbstractApiV2Controller
{
    #[OA\Get(
        path: '/api/v2/galactapedia',
        tags: ['Galactapedia', 'RSI-Website'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/limit'),
            new OA\Parameter(
                name: 'include',
                in: 'query',
                schema: new OA\Schema(
                    schema: 'galacapedia_includes_v2',
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
            ),
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
    public function index(): AnonymousResourceCollection
    {
        $query = QueryBuilder::for(Article::class)
            ->limit($this->limit)
            ->allowedIncludes(ArticleResource::validIncludes())
            ->orderByDesc('id')
            ->paginate()
            ->appends(request()->query());

        return ArticleResource::collection($query);
    }

    #[OA\Get(
        path: '/api/v2/galactapedia/{id}',
        tags: ['Galactapedia', 'RSI-Website'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/limit'),
            new OA\Parameter(ref: '#/components/schemas/galacapedia_includes_v2'),
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
    public function show($article, Request $request): AbstractBaseResource
    {
        ['article' => $article] = Validator::validate(
            [
                'article' => $article,
            ],
            [
                'article' => 'required|string|min:10|max:12',
            ]
        );

        $article = urldecode($article);

        try {
            $model = QueryBuilder::for(Article::class)
                ->where('cig_id', $article)
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

        $query = urldecode($request->get('query'));
        $queryBuilder = QueryBuilder::for(Article::class)
            ->where('title', 'like', "%{$query}%")
            ->orWhere('slug', 'like', "%{$query}%")
            ->orWhere('cig_id', 'like', "%{$query}%")
            ->orWhereHas('templates', function (Builder $builder) use ($query) {
                return $builder->where('template', 'like', "%{$query}%");
            });

        if ($queryBuilder->count() === 0) {
            throw new NotFoundHttpException('No Article(s) for specified query found.');
        }

        return ArticleResource::collection($query);
    }
}
