<?php declare(strict_types = 1);

namespace App\Http\Controllers\Api\V1\Rsi\CommLink\Series;

use App\Http\Controllers\Api\AbstractApiController as ApiController;
use App\Models\Rsi\CommLink\Series\Series;
use App\Transformers\Api\V1\Rsi\CommLink\CommLinkTransformer;
use App\Transformers\Api\V1\Rsi\CommLink\Series\SeriesTransformer;
use Dingo\Api\Contract\Http\Request;
use Dingo\Api\Http\Response;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use League\Fractal\TransformerAbstract;

/**
 * Class Series Controller
 */
class SeriesController extends ApiController
{
    /**
     * Comm-Link Transformer
     *
     * @var SeriesTransformer
     */
    protected TransformerAbstract $transformer;

    /**
     * StatsAPIController constructor.
     *
     * @param Request           $request
     * @param SeriesTransformer $transformer
     */
    public function __construct(Request $request, SeriesTransformer $transformer)
    {
        $this->transformer = $transformer;
        parent::__construct($request);
    }

    /**
     * Ausgabe aller Serien
     *
     * @return Response
     */
    public function index(): Response
    {
        $categories = Series::query()->orderBy('name');

        return $this->getResponse($categories);
    }

    /**
     * @param string $series
     *
     * @return Response
     */
    public function show(string $series): Response
    {
        try {
            $series = Series::query()->where('name', $series)->orWhere('slug', $series)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            $this->response->errorNotFound(sprintf(static::NOT_FOUND_STRING, $series));
        }

        $this->transformer = new CommLinkTransformer();

        return $this->getResponse($series->commLinks()->orderByDesc('cig_id'));
    }
}
