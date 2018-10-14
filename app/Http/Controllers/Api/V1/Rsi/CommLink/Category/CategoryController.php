<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 27.09.2018
 * Time: 10:29
 */

namespace App\Http\Controllers\Api\V1\Rsi\CommLink\Category;

use App\Http\Controllers\Api\AbstractApiController as ApiController;
use App\Models\Rsi\CommLink\Category\Category;
use App\Transformers\Api\V1\Rsi\CommLink\Category\CategoryTransformer;
use App\Transformers\Api\V1\Rsi\CommLink\CommLinkTransformer;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

/**
 * Class Category Controller
 */
class CategoryController extends ApiController
{
    /**
     * Comm-Link Transformer
     *
     * @var \App\Transformers\Api\V1\Rsi\CommLink\Category\CategoryTransformer
     */
    protected $transformer;

    /**
     * StatsAPIController constructor.
     *
     * @param \Illuminate\Http\Request                                           $request
     * @param \App\Transformers\Api\V1\Rsi\CommLink\Category\CategoryTransformer $transformer
     */
    public function __construct(Request $request, CategoryTransformer $transformer)
    {
        $this->transformer = $transformer;
        parent::__construct($request);
    }

    /**
     * Ausgabe aller Comm-Links
     *
     * @return \Dingo\Api\Http\Response
     */
    public function index()
    {
        $categories = Category::query()->orderBy('name');

        return $this->getResponse($categories);
    }

    /**
     * @param string $category
     *
     * @return \Dingo\Api\Http\Response
     */
    public function show(string $category)
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
