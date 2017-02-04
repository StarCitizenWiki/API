<?php

namespace App\Http\Controllers\StarCitizen;

use App\Exceptions\InvalidDataException;
use App\Repositories\StarCitizen\APIv1\Stats\StatsRepository;
use App\Http\Controllers\Controller;
//use Illuminate\Http\Request;
//use Illuminate\Support\Facades\Route;

class StatsAPIController extends Controller
{
    /** @var StatsRepository */
    private $_api;

    public function __construct(StatsRepository $api)
    {
        $this->_api = $api;
	    //Route::getCurrentRoute()->setParameter('transformator', 'StatsTransformator');
    }

    public function getStatsAsJSON() : String
    {
        try {
            return $this->_api->getCrowdfundStats()->asJSON();
        } catch (InvalidDataException $e) {
            return $e->getMessage();
        }
    }

    public function getFunds() : String
    {
        try {
            $stats = $this->_api->getCrowdfundStats()->asArray();
            return $stats['data']['funds']; /** @TODO */
        } catch (InvalidDataException $e) {
            return $e->getMessage();
        }


    }
}
