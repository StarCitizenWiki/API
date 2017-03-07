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

    public function getStatsAsJSON() : String
    {
        try {
            return $this->_api->getCrowdfundStats()->asJSON();
        } catch (InvalidDataException $e) {
            return $e->getMessage();
        }
    }

    public function getFunds()
    {
        try {
            $stats = $this->_api->getCrowdfundStats()->asArray();
             /** @TODO */
            return response()->json($stats['data'][1]['funds'], 200, [], JSON_PRETTY_PRINT);
        } catch (InvalidDataException $e) {
            return $e->getMessage();
        }
    }

    public function getAll()
    {
	    try {
            return response()->json($this->_api->getCrowdfundStats()->asArray(), 200, [], JSON_PRETTY_PRINT);
	    } catch (InvalidDataException $e) {
		    return $e->getMessage();
	    }
    }
}
