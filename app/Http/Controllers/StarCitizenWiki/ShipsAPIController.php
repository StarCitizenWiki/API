<?php

namespace App\Http\Controllers\StarCitizenWiki;

use App\Http\Controllers\Controller;
use App\Repositories\StarCitizenWiki\APIv1\ShipsRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
        $this->logger->debug('Ship requested', [
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
        $this->logger->debug('ShipList requested');

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
        $this->logger->debug('Ship search requested', [
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
