<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 27.09.2018
 * Time: 10:29
 */

namespace App\Http\Controllers\Api\V1\Rsi\CommLink;

use App\Http\Controllers\Api\AbstractApiController as ApiController;
use App\Models\Rsi\CommLink\CommLink;
use App\Transformers\Api\V1\Rsi\CommLink\CommLinkTransformer;
use Illuminate\Http\Request;

/**
 * Class CommLinkController
 */
class CommLinkController extends ApiController
{
    /**
     * Comm Link Transformer
     *
     * @var \App\Transformers\Api\V1\Rsi\CommLink\CommLinkTransformer
     */
    protected $transformer;

    /**
     * StatsAPIController constructor.
     *
     * @param \Illuminate\Http\Request                                  $request
     * @param \App\Transformers\Api\V1\Rsi\CommLink\CommLinkTransformer $transformer
     */
    public function __construct(Request $request, CommLinkTransformer $transformer)
    {
        $this->transformer = $transformer;
        parent::__construct($request);
    }

    /**
     * Ausgabe aller Comm Links
     *
     * @return \Dingo\Api\Http\Response
     */
    public function index()
    {
        $stats = CommLink::orderByDesc('cig_id');

        return $this->getResponse($stats);
    }

    /**
     * @param int $commLink
     *
     * @return \Dingo\Api\Http\Response
     */
    public function show(int $commLink)
    {
        $commLink = CommLink::query()->where('cig_id', $commLink)->firstOrFail();
        $this->transformer->setDefaultIncludes($this->transformer->getAvailableIncludes());

        return $this->getResponse($commLink);
    }
}
