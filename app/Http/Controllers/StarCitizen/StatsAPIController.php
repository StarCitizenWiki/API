<?php

namespace App\Http\Controllers\StarCitizen;

use App\Exceptions\InvalidDataException;
use App\Repositories\StarCitizen\APIv1\Stats\StatsRepository;
use App\Http\Controllers\Controller;

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
    private $_api;

    /**
     * StatsAPIController constructor.
     *
     * @param StatsRepository $api StatsRepository
     */
    public function __construct(StatsRepository $api)
    {
        $this->_api = $api;
    }

    /**
     * Wrapper for all get Calls
     *
     * @param \Closure $func Function to call
     *
     * @return \Illuminate\Http\JsonResponse|string
     */
    private function _getJsonPrettyPrintResponse($func)
    {
        try {
            return response()->json(
                $this->_api->$func()->asArray(),
                200,
                [],
                JSON_PRETTY_PRINT
            );
        } catch (InvalidDataException $e) {
            return $e->getMessage();
        }
    }

    /**
     * Returns just the Funds
     *
     * @return \Illuminate\Http\JsonResponse|string
     */
    public function getFunds()
    {
        return $this->_getJsonPrettyPrintResponse(__FUNCTION__);
    }

    /**
     * Returns just the Fleet
     *
     * @return \Illuminate\Http\JsonResponse|string
     */
    public function getFleet()
    {
        return $this->_getJsonPrettyPrintResponse(__FUNCTION__);
    }

    /**
     * Returns just the Fans
     *
     * @return \Illuminate\Http\JsonResponse|string
     */
    public function getFans()
    {
        return $this->_getJsonPrettyPrintResponse(__FUNCTION__);
    }

    /**
     * Returns all
     *
     * @return \Illuminate\Http\JsonResponse|string
     */
    public function getAll()
    {
        return $this->_getJsonPrettyPrintResponse(__FUNCTION__);
    }

    /**
     * Returns just Funds from last hours
     *
     * @return \Illuminate\Http\JsonResponse|string
     */
    public function getLastHoursFunds()
    {
        return $this->_getJsonPrettyPrintResponse("lastHours");
    }

    /**
     * Returns just Funds from last days
     *
     * @return \Illuminate\Http\JsonResponse|string
     */
    public function getLastDaysFunds()
    {
        return $this->_getJsonPrettyPrintResponse("lastDays");
    }

    /**
     * Returns just Funds from last weeks
     *
     * @return \Illuminate\Http\JsonResponse|string
     */
    public function getLastWeeksFunds()
    {
        return $this->_getJsonPrettyPrintResponse("lastWeeks");
    }

    /**
     * Returns just Funds from last months
     *
     * @return \Illuminate\Http\JsonResponse|string
     */
    public function getLastMonthsFunds()
    {
        return $this->_getJsonPrettyPrintResponse("lastMonths");

    }
}
