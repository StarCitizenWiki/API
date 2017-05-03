<?php
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
use Illuminate\Support\Facades\Log;

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
     * @param String $systemName
     *
     * @return StarmapRepository
     */
    public function getSystem(String $systemName)
    {
        $this->logger::debug('Requesting System', [
            'system' => $systemName,
        ]);

        $this->withTransformer(SystemTransformer::class)->request(
            'POST',
            'starmap/star-systems/'.$systemName,
            []
        );

        return $this;
    }

    /**
     * @param String $systemName
     *
     * @return $this
     */
    public function getAsteroidbelts(String $systemName)
    {
        $this->logger::debug('Requesting Astreoidbelts', [
            'system' => $systemName,
        ]);

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
     * @param String $systemName
     * @param String $type
     * @param String $objectName
     *
     * @return StarmapRepository
     */
    public function getCelestialObject(String $systemName, String $type, String $objectName)
    {
        // TODO: Implement getCelestialObject() method.
        return $this;
    }

    /**
     * https://robertsspaceindustries.com/api/starmap/find
     * POST Parameter: query
     *
     * @param String $searchString
     *
     * @return StarmapRepository
     */
    public function search(String $searchString)
    {
        // TODO: Implement search() method.
        return $this;
    }

    /**
     * @return StarmapRepository
     */
    public function getSystemList()
    {
        $this->logger::debug('Requesting System List');
        $this->dataToTransform = Starsystem::all()->toArray();

        return $this->collection()->withTransformer(SystemListTransformer::class);
    }
}
