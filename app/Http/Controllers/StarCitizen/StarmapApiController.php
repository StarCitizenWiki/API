<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 11.03.2017
 * Time: 20:07
 */

namespace App\Http\Controllers\StarCitizen;

use App\Exceptions\InvalidDataException;
use App\Http\Controllers\Controller;
use App\Repositories\StarCitizen\Interfaces\StarmapRepositoryInterface;
use Illuminate\Http\Request;
use InvalidArgumentException;

/**
 * Class StarmapAPIController
 */
class StarmapApiController extends Controller
{
    /**
     * StarmapRepository
     *
     * @var StarmapRepositoryInterface
     */
    private $repository;

    /**
     * StarmapAPIController constructor.
     *
     * @param \App\Repositories\StarCitizen\Interfaces\StarmapRepositoryInterface $repository
     */
    public function __construct(StarmapRepositoryInterface $repository)
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
     *
     * @throws \App\Exceptions\WrongMethodNameException
     */
    public function getSystem(string $name)
    {
        $name = strtoupper($name);

        app('Log')::info(make_name_readable(__FUNCTION__), ['name' => $name]);

        try {
            $data = $this->repository->getSystem($name)->toArray();

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
     * Returns a list with all known Systems
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse | string
     */
    public function getSystemList(Request $request)
    {
        $this->repository->getSystemList();
        $this->repository->getTransformer()->addFilters($request);
        $data = $this->repository->toArray();

        try {
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
     * Requests the given System Name Asteroid belts
     *
     * @param string $name SystemName
     *
     * @return \Illuminate\Http\JsonResponse | string
     *
     * @throws \App\Exceptions\WrongMethodNameException
     */
    public function getAsteroidbelts(string $name)
    {
        app('Log')::info(make_name_readable(__FUNCTION__), ['name' => $name]);

        $name = strtoupper($name);

        try {
            $data = $this->repository->getAsteroidbelts($name)->toArray();

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
                'name' => $name,
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
                'name' => $name,
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
                'name' => $name,
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
                'name' => $name,
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
                'name' => $name,
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
                'name' => $name,
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
                'name' => $name,
            ]
        );

        try {
            $objectNames = explode('.', $name);
            if (is_null($objectNames) || count($objectNames) !== 3) {
                throw new InvalidArgumentException(
                    "Objectname not like SYSTEM.TYPE.NAME (e.g. STANTON.PLANETS.STANTONIIIARCCORP). Input was {$name}"
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
     * @param string $searchString
     *
     * @return \Illuminate\Http\JsonResponse | string
     */
    public function searchStarmap(string $searchString)
    {
        $searchString = strtoupper($searchString);

        app('Log')::debug(
            'Searching Starmap requested',
            [
                'method' => __METHOD__,
                'searchstring' => $searchString,
            ]
        );

        try {
            return response()->json(
                $this->repository->search($searchString)->toArray(),
                200,
                [],
                JSON_PRETTY_PRINT
            );
        } catch (InvalidDataException $e) {
            return $e->getMessage();
        }
    }
}
