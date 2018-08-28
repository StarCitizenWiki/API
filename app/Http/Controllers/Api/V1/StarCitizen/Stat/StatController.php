<?php declare(strict_types = 1);

namespace App\Http\Controllers\Api\V1\StarCitizen\Stat;

use App\Http\Controllers\Api\AbstractApiController as ApiController;
use App\Models\Api\StarCitizen\Stat\Stat;
use App\Transformers\Api\V1\StarCitizen\Stat\StatTransformer;
use Illuminate\Http\Request;

/**
 * Stat API
 * Ausgabe des aktuellen Spendenstatus, der Fans sowie der Fleet (Crowdfund Stats)
 * Import der Statistik erfolgt täglich um 20:00
 */
class StatController extends ApiController
{
    /**
     * StatsRepository
     *
     * @var \App\Transformers\Api\V1\StarCitizen\Stat\StatTransformer
     */
    protected $transformer;

    /**
     * StatsAPIController constructor.
     *
     * @param \Illuminate\Http\Request                                  $request
     * @param \App\Transformers\Api\V1\StarCitizen\Stat\StatTransformer $transformer
     */
    public function __construct(Request $request, StatTransformer $transformer)
    {
        $this->transformer = $transformer;
        parent::__construct($request);
    }

    /**
     * Ausgabe der aktuellen Statistik
     *
     * @return \Dingo\Api\Http\Response
     */
    public function latest()
    {
        $stat = Stat::orderByDesc('created_at')->first();

        return $this->getResponse($stat);
    }

    /**
     * Ausgabe aller Statistiken
     *
     * @return \Dingo\Api\Http\Response
     */
    public function index()
    {
        $stats = Stat::orderByDesc('created_at');

        return $this->getResponse($stats);
    }
}
