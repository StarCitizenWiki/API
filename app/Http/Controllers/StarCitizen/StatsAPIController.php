<?php

namespace App\Http\Controllers\StarCitizen;

use App\Repositories\StarCitizen\APIv1\Stats\StatsRepository;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StatsAPIController extends Controller
{
    private $_api;

    public function __construct(StatsRepository $api)
    {
        $this->_api = $api;
    }

    public function getStatsAsJSON()
    {
        $response = $this->_api->getCrowdfundStats();
        return $response->getBody()->getContents();
    }
}
