<?php declare(strict_types = 1);

namespace App\Http\Controllers\StarCitizenWiki;

use App\Http\Controllers\Controller;
use App\Repositories\StarCitizenWiki\APIv1\ShipsRepository;
use App\Traits\ProfilesMethodsTrait;
use Illuminate\Http\Request;

/**
 * Class ShipsAPIController
 *
 * @package App\Http\Controllers\StarCitizenWiki
 */
class ShipsAPIController extends Controller
{
    use ProfilesMethodsTrait;

    /**
     * ShipsRepository
     *
     * @var \App\Repositories\StarCitizenWiki\APIv1\ShipsRepository
     */
    private $repository;

    /**
     * ShipsAPIController constructor.
     *
     * @param \App\Repositories\StarCitizenWiki\APIv1\ShipsRepository $repository ShipsRepository
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
        $this->startProfiling(__FUNCTION__);

        app('Log')::info(make_name_readable(__FUNCTION__), ['name' => $name]);
        $this->repository->getShip($request, $name);

        $this->stopProfiling(__FUNCTION__);

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
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getShipList(Request $request)
    {
        $this->startProfiling(__FUNCTION__);

        $this->repository->getShipList();
        $this->repository->transformer->addFilters($request);
        $data = $this->repository->asArray();

        $this->stopProfiling(__FUNCTION__);

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
        $this->startProfiling(__FUNCTION__);

        app('Log')::debug('Ship search requested', ['query' => $request->get('query')]);

        $this->validate(
            $request,
            [
                'query' => 'present|alpha_dash',
            ]
        );
        $shipName = $request->input('query');
        $data = $this->repository->searchShips($shipName)->asArray();

        $this->stopProfiling(__FUNCTION__);

        return response()->json(
            $data,
            200,
            [],
            JSON_PRETTY_PRINT
        );
    }
}
