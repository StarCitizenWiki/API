<?php
/**
 * User: Hannes
 * Date: 11.03.2017
 * Time: 20:07
 */

namespace App\Http\Controllers\StarCitizen;

use App\Exceptions\InvalidDataException;
use App\Http\Controllers\Controller;
use App\Repositories\StarCitizen\APIv1\StarmapRepository;
use App\Traits\ProfilesMethodsTrait;
use Illuminate\Http\Request;

/**
 * Class StarmapAPIController
 *
 * @package App\Http\Controllers\StarCitizen
 */
class StarmapAPIController extends Controller
{
    use ProfilesMethodsTrait;

    /**
     * StarmapRepository
     *
     * @var StarmapRepository
     */
    private $repository;

    /**
     * StarmapAPIController constructor.
     *
     * @param StarmapRepository $repository StarmapRepository
     */
    public function __construct(StarmapRepository $repository)
    {
        parent::__construct();
        $this->repository = $repository;
    }

    /**
     * Requests the given System Name
     *
     * @param String $name SystemName
     *
     * @return \Illuminate\Http\JsonResponse|string
     */
    public function getSystem(String $name)
    {
        $this->startProfiling(__FUNCTION__);

        $name = strtoupper($name);

        app('Log')::info(make_name_readable(__FUNCTION__), ['name' => $name]);

        try {
            $this->addTrace(__FUNCTION__, "Getting System with Name: {$name}", __LINE__);
            $data = $this->repository->getSystem($name)->asArray();
            $this->addTrace(__FUNCTION__, "Got System", __LINE__);
            $this->stopProfiling(__FUNCTION__);

            return response()->json(
                $data,
                200,
                [],
                JSON_PRETTY_PRINT
            );
        } catch (InvalidDataException $e) {
            $this->addTrace(__FUNCTION__, "Getting System failed with Message {$e->getMessage()}", __LINE__);
            $this->stopProfiling(__FUNCTION__);

            return $e->getMessage();
        }
    }

    /**
     * Returns a list with all known Systems
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse|string
     */
    public function getSystemList(Request $request)
    {
        $this->startProfiling(__FUNCTION__);

        $this->repository->getSystemList();
        $this->repository->transformer->addFilters($request);
        $data = $this->repository->asArray();

        try {
            $this->stopProfiling(__FUNCTION__);

            return response()->json(
                $data,
                200,
                [],
                JSON_PRETTY_PRINT
            );
        } catch (InvalidDataException $e) {
            $this->addTrace(__FUNCTION__, "Getting System-List failed with Message {$e->getMessage()}", __LINE__);
            $this->stopProfiling(__FUNCTION__);

            return $e->getMessage();
        }
    }

    /**
     * Requests the given System Name Asteroid belts
     *
     * @param String $name SystemName
     *
     * @return \Illuminate\Http\JsonResponse|string
     */
    public function getAsteroidbelts(String $name)
    {
        $this->startProfiling(__FUNCTION__);

        app('Log')::info(make_name_readable(__FUNCTION__), ['name' => $name]);

        $name = strtoupper($name);

        try {
            $this->addTrace(__FUNCTION__, "Getting Asteroidbelt", __LINE__);
            $data = $this->repository->getAsteroidbelts($name)->asArray();
            $this->stopProfiling(__FUNCTION__);

            return response()->json(
                $data,
                200,
                [],
                JSON_PRETTY_PRINT
            );
        } catch (InvalidDataException $e) {
            $this->addTrace(__FUNCTION__, "Failed getting Asteroidbelt with Message: {$e->getMessage()}", __LINE__);
            $this->stopProfiling(__FUNCTION__);

            return $e->getMessage();
        }
    }
}
