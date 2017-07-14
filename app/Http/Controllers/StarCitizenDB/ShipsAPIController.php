<?php

namespace App\Http\Controllers\StarCitizenDB;

use App\Facades\Log;
use App\Http\Controllers\Controller;
use App\Repositories\StarCitizenDB\ShipsRepository;
use Illuminate\Http\Request;

/**
 * Class ShipsAPIController
 *
 * @package App\Http\Controllers\StarCitizenDB
 */
class ShipsAPIController extends Controller
{
    /**
     * ShipsRepository
     *
     * @var ShipsRepository
     */
    private $repository;

    /**
     * ShipsAPIController constructor.
     *
     * @param ShipsRepository $repository ShipsRepository
     */
    public function __construct(ShipsRepository $repository)
    {
        parent::__construct();
        $this->repository = $repository;
    }

    /**
     * Returns a Ship by its name
     *
     * @param Request $request
     * @param String  $name    ShipName
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getShip(Request $request, String $name)
    {
        Log::debug('Ship requested', [
            'name' => $name,
        ]);

        $this->repository->getShip($request, $name);

        return response()->json(
            $this->repository->asArray(),
            200,
            [],
            JSON_PRETTY_PRINT
        );
    }

    /**
     * Returns all ships as a list
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getShipList(Request $request)
    {
        Log::debug('ShipList requested');

        $this->repository->getShipList();
        $this->repository->transformer->addFilters($request);

        return response()->json(
            $this->repository->asArray(),
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
        Log::debug('Ship search requested', [
            'query' => $request->get('query'),
        ]);

        $this->validate($request, [
            'query' => 'present|alpha_dash',
        ]);
        $shipName = $request->input('query');

        return response()->json(
            $this->repository->searchShips($shipName)->asArray(),
            200,
            [],
            JSON_PRETTY_PRINT
        );
    }
}
