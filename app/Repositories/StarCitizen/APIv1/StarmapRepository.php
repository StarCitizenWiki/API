<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 11.03.2017
 * Time: 20:04
 */

namespace App\Repositories\StarCitizen\APIv1;

use App\Models\CelestialObject;
use App\Models\Starsystem;
use App\Repositories\StarCitizen\AbstractStarCitizenRepository;
use App\Repositories\StarCitizen\Interfaces\StarmapInterface;
use App\Transformers\StarCitizen\Starmap\AsteroidbeltsTransformer;
use App\Transformers\StarCitizen\Starmap\CelestialObjectTransformer;
use App\Transformers\StarCitizen\Starmap\JumppointsTransformer;
use App\Transformers\StarCitizen\Starmap\LandingzonesTransformer;
use App\Transformers\StarCitizen\Starmap\MoonsTransformer;
use App\Transformers\StarCitizen\Starmap\PlanetsTransformer;
use App\Transformers\StarCitizen\Starmap\SpacestationsTransformer;
use App\Transformers\StarCitizen\Starmap\StarsTransformer;
use App\Transformers\StarCitizen\Starmap\SystemListTransformer;
use App\Transformers\StarCitizen\Starmap\SystemTransformer;
use InvalidArgumentException;

/**
 * Class StarmapRepository
 *
 * @package App\Repositories\StarCitizen\APIv1\Starmap
 */
class StarmapRepository extends AbstractStarCitizenRepository implements StarmapInterface
{
    const TIME_GROUP_FIELD = 'cig_time_modified';

    /**
     * https://robertsspaceindustries.com/api/starmap/star-systems/{SYSTEM}
     *
     * @param string $systemName
     *
     * @return \App\Repositories\StarCitizen\APIv1\StarmapRepository
     */
    public function getSystem(string $systemName)
    {
        app('Log')::info(make_name_readable(__FUNCTION__), ['system' => $systemName]);

        $systemQueryData = Starsystem::where('code', $systemName)
            ->orderBy(self::TIME_GROUP_FIELD, 'DESC')
            ->firstOrFail();

        return $this->withTransformer(SystemTransformer::class)->transform($systemQueryData->toArray());
    }

    /**
     * https://robertsspaceindustries.com/api/starmap/star-systems/{SYSTEM}/asteroidbelts
     * @param string $systemName
     *
     * @return \App\Repositories\StarCitizen\APIv1\StarmapRepository
     */
    public function getAsteroidbelts(string $systemName)
    {
        app('Log')::info(make_name_readable(__FUNCTION__), ['system' => $systemName]);

        $where = function ($query) {
            $query->where('type', 'ASTEROID_FIELD')->orWhere('type', 'ASTEROID_BELT');
        };

        $celestialObjectQueryData = $this->getQueryData($systemName, $where);

        return $this->withTransformer(AsteroidbeltsTransformer::class)->transform($celestialObjectQueryData);
    }

    /**
     * https://robertsspaceindustries.com/api/starmap/star-systems/{SYSTEM}/spacestations
     * @param string $systemName
     *
     * @return \App\Repositories\StarCitizen\APIv1\StarmapRepository
     */
    public function getSpacestations(string $systemName)
    {
        app('Log')::info(make_name_readable(__FUNCTION__), ['systemName' => $systemName]);

        $celestialObjectQueryData = $this->getQueryData($systemName, 'MANMADE');

        return $this->withTransformer(SpacestationsTransformer::class)->transform($celestialObjectQueryData);
    }

    /**
     * https://robertsspaceindustries.com/api/starmap/star-systems/{SYSTEM}/jumppoints
     * @param string $systemName
     *
     * @return $this
     */
    public function getJumppoints(string $systemName)
    {
        app('Log')::info(make_name_readable(__FUNCTION__), ['systemName' => $systemName]);

        $celestialObjectQueryData = $this->getQueryData($systemName, 'JUMPPOINT');

        return $this->withTransformer(JumppointsTransformer::class)->transform($celestialObjectQueryData);
    }

    /**
     * https://robertsspaceindustries.com/api/starmap/star-systems/{SYSTEM}/planets
     * @param string $systemName
     *
     * @return \App\Repositories\StarCitizen\APIv1\StarmapRepository
     */
    public function getPlanets(string $systemName)
    {
        app('Log')::info(make_name_readable(__FUNCTION__), ['systemName' => $systemName]);

        $celestialObjectQueryData = $this->getQueryData($systemName, 'PLANET');

        return $this->withTransformer(PlanetsTransformer::class)->transform($celestialObjectQueryData);
    }

    /**
     * https://robertsspaceindustries.com/api/starmap/star-systems/{SYSTEM}/moons
     * @param string $systemName
     *
     * @return \App\Repositories\StarCitizen\APIv1\StarmapRepository
     */
    public function getMoons(string $systemName)
    {
        app('Log')::info(make_name_readable(__FUNCTION__), ['systemName' => $systemName]);

        $celestialObjectQueryData = $this->getQueryData($systemName, 'SATELLITE');

        return $this->withTransformer(MoonsTransformer::class)->transform($celestialObjectQueryData);
    }

    /**
     * https://robertsspaceindustries.com/api/starmap/star-systems/{SYSTEM}/landingzones
     * @param string $systemName
     *
     * @return \App\Repositories\StarCitizen\APIv1\StarmapRepository
     */
    public function getLandingzones(string $systemName)
    {
        app('Log')::info(make_name_readable(__FUNCTION__), ['systemName' => $systemName]);

        $celestialObjectQueryData = $this->getQueryData($systemName, 'LZ');

        return $this->withTransformer(LandingzonesTransformer::class)->transform($celestialObjectQueryData);
    }

    /**
     * https://robertsspaceindustries.com/api/starmap/star-systems/{SYSTEM}/stars
     * @param string $systemName
     *
     * @return \App\Repositories\StarCitizen\APIv1\StarmapRepository
     */
    public function getStars(string $systemName)
    {
        app('Log')::info(make_name_readable(__FUNCTION__), ['systemName' => $systemName]);

        $celestialObjectQueryData = $this->getQueryData($systemName, 'STAR');

        return $this->withTransformer(StarsTransformer::class)->transform($celestialObjectQueryData);
    }

    /**
     * https://robertsspaceindustries.com/api/starmap/celestial-objects/{SYSTEM_NAME}.[TYPE}.{NAME}
     *
     * @param string $objectName
     *
     * @return \App\Repositories\StarCitizen\APIv1\StarmapRepository
     */
    public function getCelestialObject(string $objectName)
    {
        app('Log')::info(make_name_readable(__FUNCTION__), ['objectName' => $objectName]);

        $celestialObjectQueryData = CelestialObject::where('CODE', $objectName)
            ->orderBy(self::TIME_GROUP_FIELD, 'DESC')
            ->firstOrFail();

        return $this->withTransformer(CelestialObjectTransformer::class)->transform(
            $celestialObjectQueryData->toArray()
        );
    }

    /**
     * https://robertsspaceindustries.com/api/starmap/find
     * POST Parameter: query
     *
     * @param string $searchString
     *
     * @return \App\Repositories\StarCitizen\APIv1\StarmapRepository
     */
    public function search(string $searchString)
    {
        app('Log')::info(make_name_readable(__FUNCTION__), ['searchString' => $searchString]);

        $celestialObjectQueryData = CelestialObject::where('code', 'LIKE', '%'.$searchString.'%')
            ->groupBy('code')
            ->havingRaw(self::TIME_GROUP_FIELD.' = max('.self::TIME_GROUP_FIELD.')')
            ->get();

        return $this->withTransformer(CelestialObjectTransformer::class)->transform(
            $celestialObjectQueryData->toArray()
        );
    }

    /**
     * @return \App\Repositories\StarCitizen\APIv1\StarmapRepository
     */
    public function getSystemList()
    {
        app('Log')::info(make_name_readable(__FUNCTION__));
        $this->dataToTransform = Starsystem::all()->toArray();

        return $this->collection()->withTransformer(SystemListTransformer::class);
    }

    /**
     * @return \App\Repositories\StarCitizen\APIv1\StarmapRepository
     */
    public function getCelestialObjectList()
    {
        app('Log')::info(make_name_readable(__FUNCTION__));
        $this->dataToTransform = CelestialObject::all()->toArray();

        return $this->collection()->withTransformer(CelestialObjectTransformer::class);
    }

    /**
     * @param string $systemName
     *
     * @return int cig_id for SystemName
     */
    private function getCigSystemId(string $systemName): int
    {
        $systemQueryData = Starsystem::where('code', $systemName)
            ->orderBy(self::TIME_GROUP_FIELD, 'DESC')
            ->firstOrFail();
        $system = $systemQueryData->toArray();

        return (int) $system['cig_id'];
    }

    /**
     * @param string            $systemName
     * @param \Closure | string $where
     *
     * @return array
     */
    private function getQueryData(string $systemName, $where): array
    {
        $cigSystemId = $this->getCigSystemId($systemName);
        $celestialObjectQueryData = CelestialObject::where('cig_system_id', $cigSystemId);

        if (is_string($where)) {
            $celestialObjectQueryData->where('type', $where);
        } elseif (is_callable($where)) {
            $celestialObjectQueryData->where($where);
        } else {
            throw new InvalidArgumentException('type Parameter must be of type String or Closure');
        }

        $celestialObjectQueryData->groupBy('code')->havingRaw(
            self::TIME_GROUP_FIELD.' = max('.self::TIME_GROUP_FIELD.')'
        );

        return $celestialObjectQueryData->get()->toArray();
    }
}
