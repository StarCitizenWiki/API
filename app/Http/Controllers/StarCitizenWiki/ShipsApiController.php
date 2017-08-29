<?php declare(strict_types = 1);

namespace App\Http\Controllers\StarCitizenWiki;

use App\Http\Controllers\Controller;
use App\Repositories\StarCitizenWiki\ApiV1\ShipsRepository;
use App\Traits\CachesResponseTrait as CachesResponse;
use App\Traits\ProfilesMethodsTrait as ProfilesMethods;
use Illuminate\Http\Request;

/**
 * Class ShipsAPIController
 *
 * @package App\Http\Controllers\StarCitizenWiki
 */
class ShipsApiController extends Controller
{
    use ProfilesMethods;
    use CachesResponse;

    /**
     * ShipsRepository
     *
     * @var \App\Repositories\StarCitizenWiki\ApiV1\ShipsRepository
     */
    private $repository;

    /**
     * ShipsAPIController constructor.
     *
     * @param \App\Repositories\StarCitizenWiki\ApiV1\ShipsRepository $repository ShipsRepository
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
        if ($this->isCached()) {
            return $this->getCachedResponse();
        }

        $this->startProfiling(__FUNCTION__);
        app('Log')::info(make_name_readable(__FUNCTION__), ['name' => $name]);

        $this->repository->getShip($request, $name);

        $this->stopProfiling(__FUNCTION__);

        return $this->jsonResponse($this->repository->toArray());
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
        if ($this->isCached()) {
            return $this->getCachedResponse();
        }

        $this->startProfiling(__FUNCTION__);

        $this->repository->getShipList();
        $this->repository->transformer->addFilters($request);
        $data = $this->repository->toArray();

        $this->stopProfiling(__FUNCTION__);

        return $this->jsonResponse($data);
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
        if ($this->isCached()) {
            return $this->getCachedResponse();
        }

        $this->startProfiling(__FUNCTION__);

        app('Log')::debug('Ship search requested', ['query' => $request->get('query')]);

        $this->validate(
            $request,
            [
                'query' => 'present|alpha_dash',
            ]
        );
        $shipName = $request->input('query');
        $data = $this->repository->searchShips($shipName)->toArray();

        $this->stopProfiling(__FUNCTION__);

        return $this->jsonResponse($data);
    }
}
