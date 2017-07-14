<?php

namespace App\Http\Controllers\StarCitizen;

use App\Exceptions\InvalidDataException;
use App\Facades\Log;
use App\Http\Controllers\Controller;
use App\Repositories\StarCitizen\APIv1\StatsRepository;
use Illuminate\Http\Request;

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

    private $request;

    /**
     * StatsAPIController constructor.
     *
     * @param Request         $request
     * @param StatsRepository $repository StatsRepository
     */
    public function __construct(Request $request, StatsRepository $repository)
    {
        parent::__construct();
        $this->repository = $repository;
        $this->request = $request;
    }

    /**
     * Returns just the Funds
     *
     * @return \Illuminate\Http\JsonResponse|string
     */
    public function getFunds()
    {
        Log::debug('Funds Stats requested');

        return $this->getJsonPrettyPrintResponse(__FUNCTION__);
    }

    /**
     * Returns just the Fleet
     *
     * @return \Illuminate\Http\JsonResponse|string
     */
    public function getFleet()
    {
        Log::debug('Fleet Stats requested');

        return $this->getJsonPrettyPrintResponse(__FUNCTION__);
    }

    /**
     * Returns just the Fans
     *
     * @return \Illuminate\Http\JsonResponse|string
     */
    public function getFans()
    {
        Log::debug('Fans Stats requested');

        return $this->getJsonPrettyPrintResponse(__FUNCTION__);
    }

    /**
     * Returns all
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse|string
     */
    public function getAll(Request $request)
    {
        Log::debug('All Stats requested');

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
            $this->repository->$func();
            if (method_exists($this->repository->transformer, 'addFilters')) {
                $this->repository->transformer->addFilters($this->request);
            }

            return response()->json(
                $this->repository->asArray(),
                200,
                [],
                JSON_PRETTY_PRINT
            );
        } catch (InvalidDataException $e) {
            return $e->getMessage();
        }
    }
}
