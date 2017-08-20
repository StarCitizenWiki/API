<?php
/**
 * User: Keonie
 * Date: 06.08.2017 17:41
 */

namespace App\Http\Controllers\StarCitizen;

use App\Exceptions\InvalidDataException;
use App\Http\Controllers\Controller;
use App\Repositories\StarCitizen\APIv1\JumppointTunnelRepository;
use App\Traits\ProfilesMethodsTrait;
use InvalidArgumentException;

/**
 * Class JumppointTunnelAPIController
 * @package App\Http\Controllers\StarCitizen
 */
class JumppointTunnelAPIController extends Controller
{
    use ProfilesMethodsTrait;

    /**
     * @var \App\Repositories\StarCitizen\APIv1\JumppointTunnelRepository
     */
    private $repository;

    /**
     * JumppointTunnelAPIController constructor.
     *
     * @param \App\Repositories\StarCitizen\APIv1\JumppointTunnelRepository $repository
     */
    public function __construct(JumppointTunnelRepository $repository)
    {
        parent::__construct();
        $this->repository = $repository;
    }

    /**
     * Get List of all Jumppoint Tunnels
     * @return \Illuminate\Http\JsonResponse|string
     */
    public function getJumppointtunnels()
    {
        $this->startProfiling(__FUNCTION__);
        app('Log')::info(make_name_readable(__FUNCTION__));

        try {
            $this->addTrace("Getting Jumppointtunnel List", __FUNCTION__, __LINE__);
            $data = $this->repository->getJumppointTunnelList()->asArray();
            $this->addTrace("Got Jumppointtunnel List", __FUNCTION__, __LINE__);
            $this->stopProfiling(__FUNCTION__);

            return response()->json(
                $data,
                200,
                [],
                JSON_PRETTY_PRINT
            );
        } catch (InvalidDataException $e) {
            $this->addTrace("Getting Jumppointtunnel List failed with Message {$e->getMessage()}", __FUNCTION__, __LINE__);
            $this->stopProfiling(__FUNCTION__);

            return $e->getMessage();
        }
    }

    /**
     * Get one Jumppoint Tunnel by Id
     * @param $id
     *
     * @return \Illuminate\Http\JsonResponse|string
     */
    public function getJumppointTunnelById($id)
    {
        $this->startProfiling(__FUNCTION__);
        app('Log')::info(make_name_readable(__FUNCTION__), ['cig_id' => $id]);

        try {
            $this->addTrace("Getting JumppointtunnelById {$id}", __FUNCTION__, __LINE__);
            $data = $this->repository->getJumppointTunnelById($id)->asArray();

            $this->addTrace("Got JumppointtunnelById {$id}", __FUNCTION__, __LINE__);
            $this->stopProfiling(__FUNCTION__);

            return response()->json(
                $data,
                200,
                [],
                JSON_PRETTY_PRINT
            );
        } catch (InvalidDataException | InvalidArgumentException  $e) {
            $this->addTrace("Getting JumppointtunnelById failed with Message {$e->getMessage()}", __FUNCTION__, __LINE__);
            $this->stopProfiling(__FUNCTION__);

            return $e->getMessage();
        }
    }

    /**
     * Get Jumppoint Tunnel by System Name
     * @param $name
     *
     * @return \Illuminate\Http\JsonResponse|string
     */
    public function getJumppointTunnelBySystem($name)
    {
        $this->startProfiling(__FUNCTION__);
        app('Log')::info(make_name_readable(__FUNCTION__), ['name' => $name]);

        try {
            $this->addTrace("Getting JumppointtunnelBySystem {$name}", __FUNCTION__, __LINE__);
            $data = $this->repository->getJumppointTunnelBySystem($name)->asArray();
            $this->addTrace("Got JumppointtunnelBySystem {$name}", __FUNCTION__, __LINE__);
            $this->stopProfiling(__FUNCTION__);

            return response()->json(
                $data,
                200,
                [],
                JSON_PRETTY_PRINT
            );
        } catch (InvalidDataException | InvalidArgumentException  $e) {
            $this->addTrace("Getting JumppointtunnelBySystem failed with Message {$e->getMessage()}", __FUNCTION__, __LINE__);
            $this->stopProfiling(__FUNCTION__);

            return $e->getMessage();
        }
    }

    /**
     * Get all Jumppointtunnels by size
     * @param $size
     *
     * @return \Illuminate\Http\JsonResponse|string
     */
    public function getJumppointTunnelBySize($size)
    {
        $this->startProfiling(__FUNCTION__);
        app('Log')::info(make_name_readable(__FUNCTION__), ['size' => $size]);

        try {
            $this->addTrace("Getting JumppointtunnelBySize {$size}", __FUNCTION__, __LINE__);
            $data = $this->repository->getJumppointTunnelForBySize($size)->asArray();
            $this->addTrace("Got JumppointtunnelBySize {$size}", __FUNCTION__, __LINE__);
            $this->stopProfiling(__FUNCTION__);

            return response()->json(
                $data,
                200,
                [],
                JSON_PRETTY_PRINT
            );
        } catch (InvalidDataException | InvalidArgumentException  $e) {
            $this->addTrace("Getting JumppointtunnelBySize failed with Message {$e->getMessage()}", __FUNCTION__, __LINE__);
            $this->stopProfiling(__FUNCTION__);

            return $e->getMessage();
        }
    }
}