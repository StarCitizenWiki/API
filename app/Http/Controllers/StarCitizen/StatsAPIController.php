<?php

namespace App\Http\Controllers\StarCitizen;

use App\Exceptions\InvalidDataException;
use App\Repositories\StarCitizen\APIv1\Stats\StatsRepository;
use App\Http\Controllers\Controller;

class StatsAPIController extends Controller
{
    /** @var StatsRepository */
    private $_api;

    public function __construct(StatsRepository $api)
    {
        $this->_api = $api;
    }

	private function getJsonPrettyPrintResponse($func)
	{
		try {
			return response()->json($this->_api->$func()->getResponse()->toArray(), 200, [], JSON_PRETTY_PRINT);
		} catch (InvalidDataException $e) {
			return $e->getMessage();
		}
	}

    public function getFunds()
    {
	    return $this->getJsonPrettyPrintResponse(__FUNCTION__);
    }

    public function getFleet()
    {
	    return $this->getJsonPrettyPrintResponse(__FUNCTION__);
    }

    public function getFans()
    {
	    return $this->getJsonPrettyPrintResponse(__FUNCTION__);
    }

    public function getAll()
    {
	    return $this->getJsonPrettyPrintResponse(__FUNCTION__);
    }

	public function getLastHoursFunds()
	{
		return $this->getJsonPrettyPrintResponse("lastHours");
	}

	public function getLastDaysFunds()
	{
		return $this->getJsonPrettyPrintResponse("lastDays");
	}

	public function getLastWeeksFunds()
	{
		return $this->getJsonPrettyPrintResponse("lastWeeks");
	}

	public function getLastMonthsFunds()
	{
		return $this->getJsonPrettyPrintResponse("lastMonths");

	}
}
