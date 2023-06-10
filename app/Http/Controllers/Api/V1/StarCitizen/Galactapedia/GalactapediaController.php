<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\StarCitizen\Galactapedia;

use App\Http\Controllers\Api\AbstractApiController as ApiController;
use App\Http\Requests\StarCitizen\Galactapedia\GalactapediaSearchRequest;
use App\Models\StarCitizen\Galactapedia\Article;
use App\Transformers\Api\V1\StarCitizen\Galactapedia\ArticleTransformer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use OpenApi\Attributes as OA;

class GalactapediaController extends ApiController
{
    /**
     * @param Request $request
     * @param ArticleTransformer $transformer
     */
    public function __construct(Request $request, ArticleTransformer $transformer)
    {
        $this->transformer = $transformer;
        parent::__construct($request);
    }

    #[OA\Get(
        path: '/api/galactapedia',
        tags: ['Galactapedia', 'RSI-Website'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/limit'),
            new OA\Parameter(
                name: 'include',
                in: 'query',
                schema: new OA\Schema(
                    schema: 'galacapedia_includes',
                    description: 'Available Galactapedia includes',
                    collectionFormat: 'csv',
                    enum: [
                        'english',
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
                    items: new OA\Items(ref: '#/components/schemas/galactapedia_article')
                )
            )
        ]
    )]
    public function index(): Response
    {
        return $this->getResponse(Article::query()->orderByDesc('id'));
    }

    #[OA\Get(
        path: '/api/galactapedia/{id}',
        tags: ['Galactapedia', 'RSI-Website'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/limit'),
            new OA\Parameter(
                name: 'galactapedia_include',
                in: 'query',
                schema: new OA\Schema(
                    schema: 'galacapedia_includes',
                    description: 'Available Galactapedia includes',
                    collectionFormat: 'csv',
                    enum: [
                        'english',
                        'tags',
                        'categories',
                        'related_articles',
                        'properties',
                    ]
                ),
                allowReserved: true
            ),
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(
                    schema: 'galactapedia_id',
                    description: 'Galactapedia Article ID',
                    type: 'string',
                ),
            ),
        ],
        responses: [
            new OA\Response(
                ref: '#/components/schemas/galactapedia_article',
                response: 200,
                description: 'A singular Article',
            ),
            new OA\Response(
                response: 404,
                description: 'No Article with specified ID found.',
            )
        ]
    )]
    public function show(Request $request)
    {
        try {
            ['article' => $article] = Validator::validate(
                [
                    'article' => $request->article,
                ],
                [
                    'article' => 'required|string|min:10|max:12',
                ]
            );
        } catch (ValidationException $e) {
            return new JsonResponse([
                'code' => $e->status,
                'message' => $e->getMessage(),
            ], $e->status);
        }

        $article = urldecode($article);

        try {
            $model = Article::query()
                ->where('cig_id', $article)
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            return new Response(['code' => 404, 'message' => sprintf(static::NOT_FOUND_STRING, $article)], 404);
        }

        $this->transformer->includeAllAvailableIncludes();

        return $this->getResponse($model);
    }

    #[OA\Post(
        path: '/api/galactapedia/search',
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
                    items: new OA\Items(ref: '#/components/schemas/galactapedia_article')
                )
            ),
            new OA\Response(
                response: 404,
                description: 'No Article found.',
            )
        ],
    )]
    public function search(Request $request)
    {
        $rules = (new GalactapediaSearchRequest())->rules();
        try {
            $request->validate($rules);
        } catch (ValidationException $e) {
            return new JsonResponse([
                'code' => $e->status,
                'message' => $e->getMessage(),
            ], $e->status);
        }

        $query = urldecode($request->get('query'));
        $queryBuilder = Article::query()
            ->where('title', 'like', "%{$query}%")
            ->orWhere('slug', 'like', "%{$query}%")
            ->orWhere('cig_id', 'like', "%{$query}%")
            ->orWhereHas('templates', function (Builder $builder) use ($query) {
                return $builder->where('template', 'like', "%{$query}%");
            });

        if ($queryBuilder->count() === 0) {
            return new Response(['code' => 404, 'message' => sprintf(static::NOT_FOUND_STRING, $query)], 404);
        }

        return $this->getResponse($queryBuilder);
    }
}
