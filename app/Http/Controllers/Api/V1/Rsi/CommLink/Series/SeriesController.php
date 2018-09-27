<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 27.09.2018
 * Time: 10:29
 */

namespace App\Http\Controllers\Api\V1\Rsi\CommLink\Series;

use App\Http\Controllers\Api\AbstractApiController as ApiController;
use App\Models\Rsi\CommLink\Category\Category;
use App\Models\Rsi\CommLink\Series\Series;
use App\Transformers\Api\V1\Rsi\CommLink\Category\CategoryTransformer;
use App\Transformers\Api\V1\Rsi\CommLink\CommLinkTransformer;
use App\Transformers\Api\V1\Rsi\CommLink\Series\SeriesTransformer;
use Illuminate\Http\Request;

/**
 * Class Series Controller
 */
class SeriesController extends ApiController
{
    /**
     * Comm Link Transformer
     *
     * @var \App\Transformers\Api\V1\Rsi\CommLink\Series\SeriesTransformer
     */
    protected $transformer;

    /**
     * StatsAPIController constructor.
     *
     * @param \Illuminate\Http\Request                                       $request
     * @param \App\Transformers\Api\V1\Rsi\CommLink\Series\SeriesTransformer $transformer
     */
    public function __construct(Request $request, SeriesTransformer $transformer)
    {
        $this->transformer = $transformer;
        parent::__construct($request);
    }

    /**
     * Ausgabe aller Serien
     *
     * @return \Dingo\Api\Http\Response
     */
    public function index()
    {
        $categories = Series::query()->orderBy('name');

        return $this->getResponse($categories);
    }

    /**
     * @param string $series
     *
     * @return \Dingo\Api\Http\Response
     */
    public function show(string $series)
    {
        $series = Series::query()->where('name', $series)->orWhere('slug', $series)->firstOrFail();

        $this->transformer = new CommLinkTransformer();

        return $this->getResponse($series->commLinks()->orderByDesc('cig_id'));
    }
}
