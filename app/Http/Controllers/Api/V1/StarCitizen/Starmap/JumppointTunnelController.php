<?php declare(strict_types = 1);
/**
 * User: Keonie
 * Date: 06.08.2017 17:41
 */

namespace App\Http\Controllers\Api\StarCitizen\Starmap;

use App\Exceptions\InvalidDataException;
use App\Http\Controllers\Controller;

use App\Repositories\StarCitizen\Api\v1\Starmap\JumppointTunnelRepository;
use InvalidArgumentException;

/**
 * Class JumppointTunnelController
 */
class JumppointTunnelController extends Controller
{
    /**
     * @var \App\Repositories\StarCitizen\ApiV1\JumppointTunnelRepository
     */
    private $repository;

    /**
     * JumppointTunnelController constructor.
     *
     * @param \App\Repositories\StarCitizen\ApiV1\JumppointTunnelRepository $repository
     */
    public function __construct(JumppointTunnelRepository $repository)
    {
        parent::__construct();
        $this->repository = $repository;
    }

    /**
     * Get List of all Jumppoint Tunnels
     *
     * @return \Illuminate\Http\JsonResponse|string
     */
    public function getJumppointtunnels()
    {
        app('Log')::debug(make_name_readable(__FUNCTION__));

        try {
            $data = $this->repository->getJumppointTunnelList()->asArray();

            return response()->json(
                $data,
                200,
                [],
                JSON_PRETTY_PRINT
            );
        } catch (InvalidDataException $e) {

            return $e->getMessage();
        }
    }

    /**
     * Get one Jumppoint Tunnel by Id
     *
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse|string
     */
    public function getJumppointTunnelById($id)
    {
        app('Log')::debug(make_name_readable(__FUNCTION__), ['cig_id' => $id]);

        try {
            $data = $this->repository->getJumppointTunnelById($id)->asArray();

            return response()->json(
                $data,
                200,
                [],
                JSON_PRETTY_PRINT
            );
        } catch (InvalidArgumentException  $e) {
            return $e->getMessage();
        }
    }

    /**
     * Get Jumppoint Tunnel by System Name
     *
     * @param string $name
     *
     * @return \Illuminate\Http\JsonResponse|string
     */
    public function getJumppointTunnelBySystem($name)
    {
        app('Log')::debug(make_name_readable(__FUNCTION__), ['name' => $name]);

        try {
            $data = $this->repository->getJumppointTunnelBySystem($name)->asArray();

            return response()->json(
                $data,
                200,
                [],
                JSON_PRETTY_PRINT
            );
        } catch (InvalidArgumentException  $e) {
            return $e->getMessage();
        }
    }

    /**
     * Get all Jumppointtunnels by size
     *
     * @param string $size
     *
     * @return \Illuminate\Http\JsonResponse|string
     */
    public function getJumppointTunnelBySize($size)
    {
        app('Log')::debug(make_name_readable(__FUNCTION__), ['size' => $size]);

        try {
            $data = $this->repository->getJumppointTunnelForBySize($size)->asArray();

            return response()->json(
                $data,
                200,
                [],
                JSON_PRETTY_PRINT
            );
        } catch (InvalidArgumentException  $e) {

            return $e->getMessage();
        }
    }
}