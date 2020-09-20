<?php declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Rsi\CommLink\Category;

use App\Http\Controllers\Api\AbstractApiController as ApiController;
use App\Models\Rsi\CommLink\Category\Category;
use App\Transformers\Api\V1\Rsi\CommLink\Category\CategoryTransformer;
use App\Transformers\Api\V1\Rsi\CommLink\CommLinkTransformer;
use Dingo\Api\Contract\Http\Request;
use Dingo\Api\Http\Response;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Class Category Controller
 */
class CategoryController extends ApiController
{
    /**
     * StatsAPIController constructor.
     *
     * @param Request             $request
     * @param CategoryTransformer $transformer
     */
    public function __construct(Request $request, CategoryTransformer $transformer)
    {
        $this->transformer = $transformer;
        parent::__construct($request);
    }

    /**
     * Ausgabe aller Comm-Links
     *
     * @return Response
     */
    public function index(): Response
    {
        $categories = Category::query()->orderBy('name');

        return $this->getResponse($categories);
    }

    /**
     * @param string $category
     *
     * @return Response
     */
    public function show(string $category): Response
    {
        try {
            $category = Category::query()->where('name', $category)->orWhere('slug', $category)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            $this->response->errorNotFound(sprintf(static::NOT_FOUND_STRING, $category));
        }

        $this->transformer = new CommLinkTransformer();

        return $this->getResponse($category->commLinks()->orderByDesc('cig_id'));
    }
}
