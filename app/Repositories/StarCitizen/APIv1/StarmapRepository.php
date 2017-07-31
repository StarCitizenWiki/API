<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 11.03.2017
 * Time: 20:04
 */

namespace App\Repositories\StarCitizen\APIv1;

use App\Models\Starsystem;
use App\Repositories\StarCitizen\BaseStarCitizenAPI;
use App\Repositories\StarCitizen\Interfaces\StarmapInterface;
use App\Transformers\StarCitizen\Starmap\AsteroidbeltsTransformer;
use App\Transformers\StarCitizen\Starmap\SystemListTransformer;
use App\Transformers\StarCitizen\Starmap\SystemTransformer;

/**
 * Class StarmapRepository
 *
 * @package App\Repositories\StarCitizen\APIv1\Starmap
 */
class StarmapRepository extends BaseStarCitizenAPI implements StarmapInterface
{
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

        $this->withTransformer(SystemTransformer::class)->request(
            'POST',
            'starmap/star-systems/'.$systemName,
            []
        );

        return $this;
    }

    /**
     * @param string $systemName
     *
     * @return $this
     */
    public function getAsteroidbelts(string $systemName)
    {
        app('Log')::info(make_name_readable(__FUNCTION__), ['system' => $systemName]);

        $this->withTransformer(AsteroidbeltsTransformer::class)->request(
            'POST',
            'starmap/star-systems/'.$systemName,
            []
        );

        return $this;
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
        // TODO: Implement getCelestialObject() method.
        return $this;
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
        // TODO: Implement search() method.
        return $this;
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
}
