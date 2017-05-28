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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Class StarmapAPIController
 *
 * @package App\Http\Controllers\StarCitizen
 */
class StarmapAPIController extends Controller
{
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
        $name = strtoupper($name);

        Log::debug('Starmap System requested', [
            'method' => __METHOD__,
            'name' => $name,
        ]);

        try {
            return response()->json(
                $this->repository->getSystem($name)->asArray(),
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
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse|string
     */
    public function getSystemList(Request $request)
    {
        Log::debug('Starmap System List requested', [
            'method' => __METHOD__,
        ]);

        $this->repository->getSystemList();
        $this->repository->transformer->addFilters($request);
        try {
            return response()->json(
                $this->repository->asArray(),
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
     * @param String $name SystemName
     *
     * @return \Illuminate\Http\JsonResponse|string
     */
    public function getAsteroidbelts(String $name)
    {
        $name = strtoupper($name);

        Log::debug('Starmap System Asteroidbelts requested', [
            'method' => __METHOD__,
            'name' => $name,
        ]);

        try {
            return response()->json(
                $this->repository->getAsteroidbelts($name)->asArray(),
                200,
                [],
                JSON_PRETTY_PRINT
            );
        } catch (InvalidDataException $e) {
            return $e->getMessage();
        }
    }

    public function getSpacestations(String $name)
    {
        $name = strtoupper($name);

        Log::debug('Starmap System Spacestations requested', [
            'method' => __METHOD__,
            'name' => $name,
        ]);

        try {
            return response()->json(
                $this->repository->getSpacestations($name)->asArray(),
                200,
                [],
                JSON_PRETTY_PRINT
            );
        } catch (InvalidDataException $e) {
            return $e->getMessage();
        }
    }

    public function getJumppoints(String $name)
    {
        $name = strtoupper($name);

        Log::debug('Starmap System Jumppoints requested', [
            'method' => __METHOD__,
            'name' => $name,
        ]);

        try {
            return response()->json(
                $this->repository->getJumppoints($name)->asArray(),
                200,
                [],
                JSON_PRETTY_PRINT
            );
        } catch (InvalidDataException $e) {
            return $e->getMessage();
        }
    }

    public function getPlanets(String $name)
    {
        $name = strtoupper($name);

        Log::debug('Starmap System Planets requested', [
            'method' => __METHOD__,
            'name' => $name,
        ]);

        try {
            return response()->json(
                $this->repository->getPlanets($name)->asArray(),
                200,
                [],
                JSON_PRETTY_PRINT
            );
        } catch (InvalidDataException $e) {
            return $e->getMessage();
        }
    }

    public function getMoons(String $name)
    {
        $name = strtoupper($name);

        Log::debug('Starmap System Moons requested', [
            'method' => __METHOD__,
            'name' => $name,
        ]);

        try {
            return response()->json(
                $this->repository->getMoons($name)->asArray(),
                200,
                [],
                JSON_PRETTY_PRINT
            );
        } catch (InvalidDataException $e) {
            return $e->getMessage();
        }
    }

    public function getStars(String $name)
    {
        $name = strtoupper($name);

        Log::debug('Starmap System Suns requested', [
            'method' => __METHOD__,
            'name' => $name,
        ]);

        try {
            return response()->json(
                $this->repository->getStars($name)->asArray(),
                200,
                [],
                JSON_PRETTY_PRINT
            );
        } catch (InvalidDataException $e) {
            return $e->getMessage();
        }
    }

    public function getLandingzones(String $name)
    {
        $name = strtoupper($name);

        Log::debug('Starmap System Landingzones requested', [
            'method' => __METHOD__,
            'name' => $name,
        ]);

        try {
            return response()->json(
                $this->repository->getLandingzones($name)->asArray(),
                200,
                [],
                JSON_PRETTY_PRINT
            );
        } catch (InvalidDataException $e) {
            return $e->getMessage();
        }
    }

    public function getObject(String $objectname)
    {
        $name = strtoupper($objectname);

        Log::debug('Starmap System Object requested', [
            'method' => __METHOD__,
            'name' => $name,
        ]);

        try {
            $objectNames = explode('.', $name);
            if (is_null($objectNames) || count($objectNames) != 3) {
                throw new InvalidArgumentException('Objectname not like SYSTEM.TYPE.NAME (e.g. STANTON.PLANETS.STANTONIIIARCCORP). Input was '.$name);
            }

            return response()->json(
                $this->repository->getCelestialObject($objectNames[0], $objectNames[1], $objectNames[2])->asArray(),
                200,
                [],
                JSON_PRETTY_PRINT
            );
        } catch (InvalidDataException | InvalidArgumentException $e ) {
            return $e->getMessage();
        }
    }

    public function searchStarmap(String $searchstring)
    {
        $searchstring = strtoupper($searchstring);

        Log::debug('Searching Starmap requested', [
            'method' => __METHOD__,
            'searchstring' => $searchstring,
        ]);

        try {
            return response()->json(
                $this->repository->search($searchstring)->asArray(),
                200,
                [],
                JSON_PRETTY_PRINT
            );
        } catch (InvalidDataException $e) {
            return $e->getMessage();
        }
    }


}
