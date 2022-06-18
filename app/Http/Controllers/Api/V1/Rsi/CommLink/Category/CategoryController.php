<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Rsi\CommLink\Category;

use App\Http\Controllers\Api\AbstractApiController as ApiController;
use App\Models\Rsi\CommLink\Category\Category;
use App\Transformers\Api\V1\Rsi\CommLink\Category\CategoryTransformer;
use App\Transformers\Api\V1\Rsi\CommLink\CommLinkTransformer;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use OpenApi\Attributes as OA;

/**
 * Class Category Controller
 */
class CategoryController extends ApiController
{
    /**
     * CategoryController constructor.
     *
     * @param Request             $request
     * @param CategoryTransformer $transformer
     */
    public function __construct(Request $request, CategoryTransformer $transformer)
    {
        $this->transformer = $transformer;
        parent::__construct($request);
    }

    #[OA\Get(
        path: '/api/comm-links/categories',
        tags: ['Comm-Links', 'RSI-Website'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/limit'),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of Categories',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/comm_link_category')
                )
            ),
        ]
    )]
    public function index(): Response
    {
        $categories = Category::query()->orderBy('name');

        return $this->getResponse($categories);
    }

    #[OA\Get(
        path: '/api/comm-links/categories/{category}',
        tags: ['Comm-Links', 'RSI-Website'],
        parameters: [
            new OA\Parameter(
                name: 'category',
                description: 'Name or slug of the category',
                in: 'path',
                required: true,
            ),
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/limit'),
        ],
        responses: [
            new OA\Response(
                ref: '#/components/schemas/comm_link_category',
                response: 200,
                description: 'A singular Comm-Link Category',
            ),
            new OA\Response(
                response: 404,
                description: 'No Category with specified name found.',
            )
        ]
    )]
    public function show(string $category): Response
    {
        try {
            $category = Category::query()
                ->where('name', $category)
                ->orWhere('slug', $category)
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            $this->response->errorNotFound(sprintf(static::NOT_FOUND_STRING, $category));
        }

        $this->transformer = new CommLinkTransformer();

        return $this->getResponse($category->commLinks()->orderByDesc('cig_id'));
    }
}
