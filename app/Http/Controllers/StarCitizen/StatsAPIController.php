<?php

namespace App\Http\Controllers\StarCitizen;

use App\Exceptions\InvalidDataException;
use App\Http\Controllers\Controller;
use App\Repositories\StarCitizen\APIv1\Stats\StatsRepository;
use Illuminate\Support\Facades\Log;

/**
 * Class StatsAPIController
 *
 * @package App\Http\Controllers\StarCitizen
 */
class StatsAPIController extends Controller
{
    /**
     * StatsRepository
     *
     * @var StatsRepository
     */
    private $repository;

    /**
     * StatsAPIController constructor.
     *
     * @param StatsRepository $repository StatsRepository
     */
    public function __construct(StatsRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Returns just the Funds
     *
     * @return \Illuminate\Http\JsonResponse|string
     */
    public function getFunds()
    {
        Log::debug('Funds Stats requested', [
            'method' => __METHOD__,
        ]);

        return $this->getJsonPrettyPrintResponse(__FUNCTION__);
    }

    /**
     * Returns just the Fleet
     *
     * @return \Illuminate\Http\JsonResponse|string
     */
    public function getFleet()
    {
        Log::debug('Fleet Stats requested', [
            'method' => __METHOD__,
        ]);

        return $this->getJsonPrettyPrintResponse(__FUNCTION__);
    }

    /**
     * Returns just the Fans
     *
     * @return \Illuminate\Http\JsonResponse|string
     */
    public function getFans()
    {
        Log::debug('Fans Stats requested', [
            'method' => __METHOD__,
        ]);

        return $this->getJsonPrettyPrintResponse(__FUNCTION__);
    }

    /**
     * Returns all
     *
     * @return \Illuminate\Http\JsonResponse|string
     */
    public function getAll()
    {
        Log::debug('All Stats requested', [
            'method' => __METHOD__,
        ]);

        return $this->getJsonPrettyPrintResponse(__FUNCTION__);
    }

    /**
     * Returns just Funds from last hours
     *
     * @return \Illuminate\Http\JsonResponse|string
     */
    public function getLastHoursFunds()
    {
        return $this->getJsonPrettyPrintResponse("lastHours");
    }

    /**
     * Returns just Funds from last days
     *
     * @return \Illuminate\Http\JsonResponse|string
     */
    public function getLastDaysFunds()
    {
        return $this->getJsonPrettyPrintResponse("lastDays");
    }

    /**
     * Returns just Funds from last weeks
     *
     * @return \Illuminate\Http\JsonResponse|string
     */
    public function getLastWeeksFunds()
    {
        return $this->getJsonPrettyPrintResponse("lastWeeks");
    }

    /**
     * Returns just Funds from last months
     *
     * @return \Illuminate\Http\JsonResponse|string
     */
    public function getLastMonthsFunds()
    {
        return $this->getJsonPrettyPrintResponse("lastMonths");
    }

    /**
     * Wrapper for all get Calls
     *
     * @param \Closure | String $func Function to call
     *
     * @return \Illuminate\Http\JsonResponse|string
     */
    private function getJsonPrettyPrintResponse($func)
    {
        try {
            return response()->json(
                $this->repository->$func()->asArray(),
                200,
                [],
                JSON_PRETTY_PRINT
            );
        } catch (InvalidDataException $e) {
            return $e->getMessage();
        }
    }
}
