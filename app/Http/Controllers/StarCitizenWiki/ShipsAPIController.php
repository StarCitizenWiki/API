<?php

namespace App\Http\Controllers\StarCitizenWiki;

use App\Http\Controllers\Controller;
use App\Repositories\StarCitizenWiki\APIv1\Ships\ShipsRepository;
use Illuminate\Http\Request;

/**
 * Class ShipsAPIController
 *
 * @package App\Http\Controllers\StarCitizenWiki
 */
class ShipsAPIController extends Controller
{
    /**
     * ShipsRepository
     *
     * @var ShipsRepository
     */
    private $_api;

    /**
     * ShipsAPIController constructor.
     *
     * @param ShipsRepository $api ShipsRepository
     */
    public function __construct(ShipsRepository $api)
    {
        $this->_api = $api;
    }

    /**
     * Returns a Ship by its name
     *
     * @param String $name ShipName
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getShip(String $name)
    {
        return response()->json(
            $this->_api->getShip($name)->asArray(),
            200,
            [],
            JSON_PRETTY_PRINT
        );
    }

    /**
     * Returns all ships as a list
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getShipList()
    {
        return response()->json(
            $this->_api->getShipList()->asArray(),
            200,
            [],
            JSON_PRETTY_PRINT
        );
    }

    /**
     * Searchs for a ship by name
     *
     * @param Request $request Search Request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchShips(Request $request)
    {
        $this->validate(
            $request,
            ['query' => 'present|alpha_dash']
        );
        $shipName = $request->input('query');

        return response()->json(
            $this->_api->searchShips($shipName)->asArray(),
            200,
            [],
            JSON_PRETTY_PRINT
        );
    }
}
