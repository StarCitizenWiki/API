<?php declare(strict_types = 1);

namespace App\Http\Controllers\StarCitizenDB;

use App\Http\Controllers\Controller;
use App\Repositories\StarCitizenDB\ShipsRepository;
use Illuminate\Http\Request;

/**
 * Class ShipsAPIController
 *
 * @package App\Http\Controllers\StarCitizenDB
 */
class ShipsApiController extends Controller
{
    /**
     * ShipsRepository
     *
     * @var \App\Repositories\StarCitizenDB\ShipsRepository
     */
    private $repository;

    /**
     * ShipsAPIController constructor.
     *
     * @param \App\Repositories\StarCitizenDB\ShipsRepository $repository
     *
     */
    public function __construct(ShipsRepository $repository)
    {
        parent::__construct();
        $this->repository = $repository;
    }

    /**
     * Returns a Ship by its name
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $name    ShipName
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getShip(Request $request, string $name)
    {
        app('Log')::info(make_name_readable(__FUNCTION__), ['name' => $name]);
        $data = $this->repository->getShip($request, $name)->asArray();

        return response()->json(
            $data,
            200,
            [],
            JSON_PRETTY_PRINT
        );
    }

    /**
     * Returns all ships as a list
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getShipList(Request $request)
    {
        app('Log')::info(make_name_readable(__FUNCTION__));
        $this->repository->getShipList();
        $this->repository->getTransformer()->addFilters($request);
        $data = $this->repository->toArray();

        return response()->json(
            $data,
            200,
            [],
            JSON_PRETTY_PRINT
        );
    }

    /**
     * Searchs for a ship by name
     *
     * @param \Illuminate\Http\Request $request Search Request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchShips(Request $request)
    {
        app('Log')::info(make_name_readable(__FUNCTION__), ['query' => $request->get('query')]);

        $this->validate(
            $request,
            [
                'query' => 'present|alpha_dash',
            ]
        );
        $shipName = $request->input('query');

        $data = $this->repository->searchShips($shipName)->asArray();

        return response()->json(
            $data,
            200,
            [],
            JSON_PRETTY_PRINT
        );
    }
}
