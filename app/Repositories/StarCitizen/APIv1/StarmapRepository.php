<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 11.03.2017
 * Time: 20:04
 */

namespace App\Repositories\StarCitizen\APIv1;

use App\Models\Starsystem;
use App\Models\CelestialObject;
use App\Repositories\StarCitizen\BaseStarCitizenAPI;
use App\Repositories\StarCitizen\Interfaces\StarmapInterface;
use App\Transformers\StarCitizen\Starmap\AsteroidbeltsTransformer;
use App\Transformers\StarCitizen\Starmap\StarsTransformer;
use App\Transformers\StarCitizen\Starmap\CelestialObjectTransformer;
use App\Transformers\StarCitizen\Starmap\MoonsTransformer;
use App\Transformers\StarCitizen\Starmap\LandingzonesTransformer;
use App\Transformers\StarCitizen\Starmap\PlanetsTransformer;
use App\Transformers\StarCitizen\Starmap\SpacestationsTransformer;
use App\Transformers\StarCitizen\Starmap\JumppointsTransformer;
use App\Transformers\StarCitizen\Starmap\SystemListTransformer;
use App\Transformers\StarCitizen\Starmap\SystemTransformer;

/**
 * Class StarmapRepository
 *
 * @package App\Repositories\StarCitizen\APIv1\Starmap
 */
class StarmapRepository extends BaseStarCitizenAPI implements StarmapInterface
{
    const TIME_GROUP_FIELD = 'cig_time_modified';

    /**
     * https://robertsspaceindustries.com/api/starmap/star-systems/{SYSTEM}
     *
     * @param string $systemName
     *
     * @return StarmapRepository
     */
    public function getSystem(string $systemName)
    {
        app('Log')::info(make_name_readable(__FUNCTION__), ['system' => $systemName]);

        $systemQueryData = Starsystem::where('code', $systemName)
            ->orderby(self::TIME_GROUP_FIELD, 'DESC')
            ->firstOrFail();

        return $this->withTransformer(SystemTransformer::class)->transform($systemQueryData->toArray());
    }

    /**
     * https://robertsspaceindustries.com/api/starmap/star-systems/{SYSTEM}/asteroidbelts
     * @param String $systemName
     * @return $this
     */
    public function getAsteroidbelts(string $systemName)
    {
        app('Log')::info(make_name_readable(__FUNCTION__), ['system' => $systemName]);

        $cigSystemId = $this->getCigSystemId($systemName);
        $celestialObjectQueryData = CelestialObject::where('cig_system_id', $cigSystemId)
            ->where(function ($query) {
                $query->where('type', 'ASTEROID_FIELD')->orWhere('type', 'ASTEROID_BELT');
            })
            ->groupby('code')
            ->havingRaw(self::TIME_GROUP_FIELD .' = max(' . self::TIME_GROUP_FIELD . ')')
            ->get();
        return $this->withTransformer(AsteroidbeltsTransformer::class)->transform($celestialObjectQueryData->toArray());
    }

    /**
     * https://robertsspaceindustries.com/api/starmap/star-systems/{SYSTEM}/spacestations
     * @param String $systemName
     * @return $this
     */
    public function getSpacestations(String $systemName)
    {
        Log::debug('Requesting Spacestations', [
            'method' => __METHOD__,
            'system' => $systemName,
        ]);

        $cigSystemId = $this->getCigSystemId($systemName);
        $celestialObjectQueryData = CelestialObject::where('cig_system_id', $cigSystemId)
            ->where('type', 'MANMADE')
            ->groupby('code')
            ->havingRaw(self::TIME_GROUP_FIELD .' = max(' . self::TIME_GROUP_FIELD . ')')
            ->get();
        return $this->withTransformer(SpacestationsTransformer::class)->transform($celestialObjectQueryData->toArray());
    }

    /**
     * https://robertsspaceindustries.com/api/starmap/star-systems/{SYSTEM}/jumppoints
     * @param String $systemName
     * @return $this
     */
    public function getJumppoints(String $systemName)
    {
        Log::debug('Requesting Jumppoints', [
            'method' => __METHOD__,
            'system' => $systemName,
        ]);

        $cigSystemId = $this->getCigSystemId($systemName);
        $celestialObjectQueryData = CelestialObject::where('cig_system_id', $cigSystemId)
            ->where('type', 'JUMPPOINT')
            ->groupby('code')
            ->havingRaw(self::TIME_GROUP_FIELD .' = max(' . self::TIME_GROUP_FIELD . ')')
            ->get();
        return $this->withTransformer(JumppointsTransformer::class)->transform($celestialObjectQueryData->toArray());
    }

    /**
     * https://robertsspaceindustries.com/api/starmap/star-systems/{SYSTEM}/planets
     * @param String $systemName
     * @return $this
     */
    public function getPlanets(String $systemName)
    {
        Log::debug('Requesting Planets', [
            'method' => __METHOD__,
            'system' => $systemName,
        ]);

        $cigSystemId = $this->getCigSystemId($systemName);
        $celestialObjectQueryData = CelestialObject::where('cig_system_id', $cigSystemId)
            ->where('type', 'PLANET')
            ->groupby('code')
            ->havingRaw(self::TIME_GROUP_FIELD .' = max(' . self::TIME_GROUP_FIELD . ')')
            ->get();
        return $this->withTransformer(PlanetsTransformer::class)->transform($celestialObjectQueryData->toArray());
    }

    /**
     * https://robertsspaceindustries.com/api/starmap/star-systems/{SYSTEM}/moons
     * @param String $systemName
     * @return $this
     */
    public function getMoons(String $systemName)
    {
        Log::debug('Requesting Moons', [
            'method' => __METHOD__,
            'system' => $systemName,
        ]);

        $cigSystemId = $this->getCigSystemId($systemName);
        $celestialObjectQueryData = CelestialObject::where('cig_system_id', $cigSystemId)
            ->where('type', 'SATELLITE')
            ->groupby('code')
            ->havingRaw(self::TIME_GROUP_FIELD .' = max(' . self::TIME_GROUP_FIELD . ')')
            ->get();
        return $this->withTransformer(MoonsTransformer::class)->transform($celestialObjectQueryData->toArray());
    }

    /**
     * https://robertsspaceindustries.com/api/starmap/star-systems/{SYSTEM}/landingzones
     * @param String $systemName
     * @return $this
     */
    public function getLandingzones(String $systemName)
    {
        Log::debug('Requesting Landingzones', [
            'method' => __METHOD__,
            'system' => $systemName,
        ]);

        $cigSystemId = $this->getCigSystemId($systemName);
        $celestialObjectQueryData = CelestialObject::where('cig_system_id', $cigSystemId)
            ->where('type', 'LZ')
            ->groupby('code')
            ->havingRaw(self::TIME_GROUP_FIELD .' = max(' . self::TIME_GROUP_FIELD . ')')
            ->get();
        return $this->withTransformer(LandingzonesTransformer::class)->transform($celestialObjectQueryData->toArray());
    }

    /**
     * https://robertsspaceindustries.com/api/starmap/star-systems/{SYSTEM}/stars
     * @param String $systemName
     * @return $this
     */
    public function getStars(String $systemName)
    {
        Log::debug('Requesting Stars', [
            'method' => __METHOD__,
            'system' => $systemName,
        ]);

        $cigSystemId = $this->getCigSystemId($systemName);
        $celestialObjectQueryData = CelestialObject::where('cig_system_id', $cigSystemId)
            ->where('type', 'STAR')
            ->groupby('code')
            ->havingRaw(self::TIME_GROUP_FIELD .' = max(' . self::TIME_GROUP_FIELD . ')')
            ->get();
        return $this->withTransformer(StarsTransformer::class)->transform($celestialObjectQueryData->toArray());
    }

    /**
     * https://robertsspaceindustries.com/api/starmap/celestial-objects/{SYSTEM_NAME}.[TYPE}.{NAME}
     *
     * @param string $systemName
     * @param string $type
     * @param string $objectName
     *
     * @return StarmapRepository
     */
    public function getCelestialObject(string $systemName, string $type, string $objectName)
    {
        $celestialObjectName = $systemName . '.' . $type . '.' . $objectName;

        Log::debug('Requesting Celestial Object', [
            'method' => __METHOD__,
            'CelestialObjectname' => $celestialObjectName,
        ]);

        $celestialObjectQueryData = CelestialObject::where('CODE', $celestialObjectName)
            ->orderby(self::TIME_GROUP_FIELD, 'DESC')
            ->firstOrFail();
        return $this->withTransformer(CelestialObjectTransformer::class)->transform($celestialObjectQueryData->toArray());
    }

    /**
     * https://robertsspaceindustries.com/api/starmap/find
     * POST Parameter: query
     *
     * @param string $searchString
     *
     * @return StarmapRepository
     */
    public function search(string $searchString)
    {
        Log::debug('Requesting Search', [
            'method' => __METHOD__,
            'searchString' => $searchString,
        ]);

        $celestialObjectQueryData = CelestialObject::where('code', 'LIKE',  '%' . $searchString . '%')
            ->groupby('code')
            ->havingRaw(self::TIME_GROUP_FIELD .' = max(' . self::TIME_GROUP_FIELD . ')')
            ->get();
        return $this->withTransformer(CelestialObjectTransformer::class)->transform($celestialObjectQueryData->toArray());
    }

    /**
     * @return StarmapRepository
     */
    public function getSystemList()
    {
        app('Log')::info(make_name_readable(__FUNCTION__));
        $this->dataToTransform = Starsystem::all()->toArray();

        return $this->collection()->withTransformer(SystemListTransformer::class);
    }

    /**
     * @return StarmapRepository
     */
    public function getCelestialObjectList()
    {
        Log::debug('Requesting Celestial Objects List', [
            'method' => __METHOD__,
        ]);
        $this->dataToTransform = CelestialObject::all()->toArray();

        return $this->collection()->withTransformer(CelestialObjectTransformer::class);
    }

    /**
     * @param String $systemName
     *
     * @return int cig_id for SystemName
     */
    private function getCigSystemId(String $systemName) : int
    {
        $systemQueryData = Starsystem::where('code', $systemName)
            ->orderby(self::TIME_GROUP_FIELD, 'DESC')
            ->firstOrFail();
        $system = $systemQueryData->toArray();
        return $system['cig_id'];
    }
}
