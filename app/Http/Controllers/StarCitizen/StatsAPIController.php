<?php

namespace App\Http\Controllers\StarCitizen;

use App\Exceptions\EmptyResponseException;
use App\Repositories\StarCitizen\APIv1\Stats\StatsRepository;
use App\Http\Controllers\Controller;
use GuzzleHttp\Psr7\Response;
use Illuminate\Http\Request;

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
        return $this->_api->getCrowdfundStats()->asJSON();
    }
}
