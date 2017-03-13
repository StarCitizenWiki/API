<?php
/**
 * User: Hannes
 * Date: 11.03.2017
 * Time: 20:04
 */

namespace App\Repositories\StarCitizen\APIv1\Starmap;

use App\Repositories\StarCitizen\APIv1\BaseStarCitizenAPI;
use App\Transformers\StarCitizen\Starmap\SystemTransformer;

class StarmapRepository extends BaseStarCitizenAPI implements StarmapInterface
{

    /**
     * https://robertsspaceindustries.com/api/starmap/star-systems/{SYSTEM}
     * @param String $systemName
     * @return StarmapRepository
     */
    public function getSystem(String $systemName)
    {
        $this->withTransformer(SystemTransformer::class)->request('POST', 'starmap/star-systems/'.$systemName, []);
        return $this;
    }

    /**
     * https://robertsspaceindustries.com/api/starmap/celestial-objects/{SYSTEM_NAME}.[TYPE}.{NAME}
     * @param String $systemName
     * @param String $type
     * @param String $objectName
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
     * @param String $searchString
     * @return StarmapRepository
     */
    public function search(String $searchString)
    {
        // TODO: Implement search() method.
        return $this;
    }
}