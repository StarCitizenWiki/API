<?php declare(strict_types = 1);
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
     * @param string $name SystemName
     *
     * @return \Illuminate\Http\JsonResponse|string
     */
    public function getSystem(string $name)
    {
        $this->startProfiling(__FUNCTION__);

        $name = strtoupper($name);

        app('Log')::info(make_name_readable(__FUNCTION__), ['name' => $name]);

        try {
            $this->addTrace("Getting System with Name: {$name}", __FUNCTION__, __LINE__);
            $data = $this->repository->getSystem($name)->asArray();
            $this->addTrace("Got System", __FUNCTION__, __LINE__);
            $this->stopProfiling(__FUNCTION__);

            return response()->json(
                $data,
                200,
                [],
                JSON_PRETTY_PRINT
            );
        } catch (InvalidDataException $e) {
            $this->addTrace("Getting System failed with Message {$e->getMessage()}", __FUNCTION__, __LINE__);
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
            $this->addTrace("Getting System-List failed with Message {$e->getMessage()}", __FUNCTION__, __LINE__);
            $this->stopProfiling(__FUNCTION__);

            return $e->getMessage();
        }
    }

    /**
     * Requests the given System Name Asteroid belts
     *
     * @param string $name SystemName
     *
     * @return \Illuminate\Http\JsonResponse|string
     */
    public function getAsteroidbelts(string $name)
    {
        $this->startProfiling(__FUNCTION__);

        app('Log')::info(make_name_readable(__FUNCTION__), ['name' => $name]);

        $name = strtoupper($name);

        try {
            $this->addTrace("Getting Asteroidbelt", __FUNCTION__, __LINE__);
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
