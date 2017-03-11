<?php

namespace App\Http\Controllers\StarCitizenWiki;

use App\Http\Controllers\Controller;
use App\Repositories\StarCitizenWiki\APIv1\Ships\ShipsRepository;
use Illuminate\Http\Request;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;

class ShipsAPIController extends Controller
{
    /** @var ShipsRepository */
    private $_api;

    public function __construct(ShipsRepository $api)
    {
        $this->_api = $api;
    }

    public function getShip(String $name)
    {
        return response()->json($this->_api->getShip($name)->asArray(), 200, [], JSON_PRETTY_PRINT);
    }

    public function getShipList()
    {
        return response()->json($this->_api->getShipList()->asArray(), 200, [], JSON_PRETTY_PRINT);
    }

    public function searchShips(Request $request)
    {
        $shipName = $request->input('query');
        return response()->json($this->_api->searchShips($shipName)->asArray(), 200, [], JSON_PRETTY_PRINT);
    }
}
