<?php declare(strict_types = 1);

namespace App\Http\Controllers\Api\V1\StarCitizen\Stat;

use App\Http\Controllers\Api\AbstractApiController as ApiController;
use App\Models\Api\StarCitizen\Stat\Stat;
use App\Transformers\Api\V1\StarCitizen\Stat\StatTransformer;
use Dingo\Api\Http\Response;
use Illuminate\Http\Request;
use League\Fractal\TransformerAbstract;

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
     * @var StatTransformer
     */
    protected TransformerAbstract $transformer;

    /**
     * StatsAPIController constructor.
     *
     * @param Request         $request
     * @param StatTransformer $transformer
     */
    public function __construct(Request $request, StatTransformer $transformer)
    {
        $this->transformer = $transformer;
        parent::__construct($request);
    }

    /**
     * Ausgabe der aktuellen Statistik
     *
     * @return Response
     */
    public function latest(): Response
    {
        $stat = Stat::orderByDesc('created_at')->first();

        return $this->getResponse($stat);
    }

    /**
     * Ausgabe aller Statistiken
     *
     * @return Response
     */
    public function index(): Response
    {
        $stats = Stat::orderByDesc('created_at');

        return $this->getResponse($stats);
    }
}
