<?php
/**
 * User: Hannes
 * Date: 11.03.2017
 * Time: 20:07
 */

namespace App\Http\Controllers\StarCitizen;

use App\Exceptions\InvalidDataException;
use App\Http\Controllers\Controller;
use App\Repositories\StarCitizen\APIv1\Starmap\StarmapRepository;

class StarmapAPIController extends Controller
{
    /** @var StarmapRepository */
    private $_api;

    public function __construct(StarmapRepository $api)
    {
        $this->_api = $api;
    }

    public function getSystem(String $name)
    {
        $name = strtoupper($name);
        try {
            return response()->json($this->_api->getSystem($name)->transform()->toArray(), 200, [], JSON_PRETTY_PRINT);
        } catch (InvalidDataException $e) {
            return $e->getMessage();
        }
    }
}