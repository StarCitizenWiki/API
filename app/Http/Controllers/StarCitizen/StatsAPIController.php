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

    public function getFunds()
    {
        try {
            return response()->json($this->_api->getFunds()->getResponse()->toArray(), 200, [], JSON_PRETTY_PRINT);
        } catch (InvalidDataException $e) {
            return $e->getMessage();
        }
    }

    public function getFleet()
    {
        try {
            return response()->json($this->_api->getFleet()->getResponse()->toArray(), 200, [], JSON_PRETTY_PRINT);
        } catch (InvalidDataException $e) {
            return $e->getMessage();
        }
    }

    public function getFans()
    {
        try {
            return response()->json($this->_api->getFans()->getResponse()->toArray(), 200, [], JSON_PRETTY_PRINT);
        } catch (InvalidDataException $e) {
            return $e->getMessage();
        }
    }

    public function getAll()
    {
	    try {
            return response()->json($this->_api->getAll()->getResponse()->toArray(), 200, [], JSON_PRETTY_PRINT);
	    } catch (InvalidDataException $e) {
		    return $e->getMessage();
	    }
    }
}
