<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 11.03.2017
 * Time: 20:07
 */

namespace App\Http\Controllers\StarCitizen;

use App\Exceptions\InvalidDataException;
use App\Http\Controllers\Controller;
use App\Repositories\StarCitizen\ApiV1\StarmapRepository;
use App\Traits\ProfilesMethodsTrait;
use Illuminate\Http\Request;
use InvalidArgumentException;

/**
 * Class StarmapAPIController
 *
 * @package App\Http\Controllers\StarCitizen
 */
class StarmapApiController extends Controller
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
     * @return \Illuminate\Http\JsonResponse | string
     */
    public function getSystem(string $name)
    {
        $this->startProfiling(__FUNCTION__);

        $name = strtoupper($name);

        app('Log')::info(make_name_readable(__FUNCTION__), ['name' => $name]);

        try {
            $this->addTrace("Getting System with Name: {$name}", __FUNCTION__, __LINE__);
            $data = $this->repository->getSystem($name)->toArray();
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
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse | string
     */
    public function getSystemList(Request $request)
    {
        $this->startProfiling(__FUNCTION__);

        $this->repository->getSystemList();
        $this->repository->transformer->addFilters($request);
        $data = $this->repository->toArray();

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
     * @return \Illuminate\Http\JsonResponse | string
     */
    public function getAsteroidbelts(string $name)
    {
        $this->startProfiling(__FUNCTION__);

        app('Log')::info(make_name_readable(__FUNCTION__), ['name' => $name]);

        $name = strtoupper($name);

        try {
            $this->addTrace("Getting Asteroidbelt", __FUNCTION__, __LINE__);
            $data = $this->repository->getAsteroidbelts($name)->toArray();
            $this->stopProfiling(__FUNCTION__);

            return response()->json(
                $data,
                200,
                [],
                JSON_PRETTY_PRINT
            );
        } catch (InvalidDataException $e) {
            $this->addTrace("Failed getting Asteroidbelt with Message: {$e->getMessage()}", __FUNCTION__, __LINE__);
            $this->stopProfiling(__FUNCTION__);

            return $e->getMessage();
        }
    }

    /**
     * @param string $name
     *
     * @return \Illuminate\Http\JsonResponse | string
     */
    public function getSpacestations(string $name)
    {
        $name = strtoupper($name);

        app('Log')::debug(
            'Starmap System Spacestations requested',
            [
                'method' => __METHOD__,
                'name'   => $name,
            ]
        );

        try {
            return response()->json(
                $this->repository->getSpacestations($name)->toArray(),
                200,
                [],
                JSON_PRETTY_PRINT
            );
        } catch (InvalidDataException $e) {
            return $e->getMessage();
        }
    }

    /**
     * @param string $name
     *
     * @return \Illuminate\Http\JsonResponse | string
     */
    public function getJumppoints(string $name)
    {
        $name = strtoupper($name);

        app('Log')::debug(
            'Starmap System Jumppoints requested',
            [
                'method' => __METHOD__,
                'name'   => $name,
            ]
        );

        try {
            return response()->json(
                $this->repository->getJumppoints($name)->toArray(),
                200,
                [],
                JSON_PRETTY_PRINT
            );
        } catch (InvalidDataException $e) {
            return $e->getMessage();
        }
    }

    /**
     * @param string $name
     *
     * @return \Illuminate\Http\JsonResponse | string
     */
    public function getPlanets(string $name)
    {
        $name = strtoupper($name);

        app('Log')::debug(
            'Starmap System Planets requested',
            [
                'method' => __METHOD__,
                'name'   => $name,
            ]
        );

        try {
            return response()->json(
                $this->repository->getPlanets($name)->toArray(),
                200,
                [],
                JSON_PRETTY_PRINT
            );
        } catch (InvalidDataException $e) {
            return $e->getMessage();
        }
    }

    /**
     * @param string $name
     *
     * @return \Illuminate\Http\JsonResponse | string
     */
    public function getMoons(string $name)
    {
        $name = strtoupper($name);

        app('Log')::debug(
            'Starmap System Moons requested',
            [
                'method' => __METHOD__,
                'name'   => $name,
            ]
        );

        try {
            return response()->json(
                $this->repository->getMoons($name)->toArray(),
                200,
                [],
                JSON_PRETTY_PRINT
            );
        } catch (InvalidDataException $e) {
            return $e->getMessage();
        }
    }

    /**
     * @param string $name
     *
     * @return \Illuminate\Http\JsonResponse | string
     */
    public function getStars(string $name)
    {
        $name = strtoupper($name);

        app('Log')::debug(
            'Starmap System Suns requested',
            [
                'method' => __METHOD__,
                'name'   => $name,
            ]
        );

        try {
            return response()->json(
                $this->repository->getStars($name)->toArray(),
                200,
                [],
                JSON_PRETTY_PRINT
            );
        } catch (InvalidDataException $e) {
            return $e->getMessage();
        }
    }

    /**
     * @param string $name
     *
     * @return \Illuminate\Http\JsonResponse | string
     */
    public function getLandingzones(string $name)
    {
        $name = strtoupper($name);

        app('Log')::debug(
            'Starmap System Landingzones requested',
            [
                'method' => __METHOD__,
                'name'   => $name,
            ]
        );

        try {
            return response()->json(
                $this->repository->getLandingzones($name)->toArray(),
                200,
                [],
                JSON_PRETTY_PRINT
            );
        } catch (InvalidDataException $e) {
            return $e->getMessage();
        }
    }

    /**
     * @param string $objectname
     *
     * @return \Illuminate\Http\JsonResponse | string
     */
    public function getObject(string $objectname)
    {
        $name = strtoupper($objectname);

        app('Log')::debug(
            'Starmap System Object requested',
            [
                'method' => __METHOD__,
                'name'   => $name,
            ]
        );

        try {
            $objectNames = explode('.', $name);
            if (is_null($objectNames) || count($objectNames) != 3) {
                throw new InvalidArgumentException(
                    'Objectname not like SYSTEM.TYPE.NAME (e.g. STANTON.PLANETS.STANTONIIIARCCORP). Input was '.$name
                );
            }

            return response()->json(
                $this->repository->getCelestialObject($name)->toArray(),
                200,
                [],
                JSON_PRETTY_PRINT
            );
        } catch (InvalidDataException | InvalidArgumentException $e) {
            return $e->getMessage();
        }
    }

    /**
     * @param string $searchstring
     *
     * @return \Illuminate\Http\JsonResponse | string
     */
    public function searchStarmap(string $searchstring)
    {
        $searchstring = strtoupper($searchstring);

        app('Log')::debug(
            'Searching Starmap requested',
            [
                'method' => __METHOD__,
                'searchstring' => $searchstring,
            ]
        );

        try {
            return response()->json(
                $this->repository->search($searchstring)->toArray(),
                200,
                [],
                JSON_PRETTY_PRINT
            );
        } catch (InvalidDataException $e) {
            return $e->getMessage();
        }
    }
}
