<?php
/**
 * User: Hannes
 * Date: 19.01.2017
 * Time: 13:09
 */

namespace App\Repositories\StarCitizen\APIv1\Starmap;

/**
 * Interface StarmapInterface
 *
 * @package App\Repositories\StarCitizen\APIv1\Starmap
 */
interface StarmapInterface
{
    /**
     * https://robertsspaceindustries.com/api/starmap/star-systems/{SYSTEM}
     * @param String $systemName
     *
     * @return StarmapRepository
     */
    public function getSystem(String $systemName);

    /**
     * https://robertsspaceindustries.com/api/starmap/celestial-objects/{SYSTEM_NAME}.[TYPE}.{NAME}
     * @param String $systemName
     * @param String $type
     * @param String $objectName
     *
     * @return StarmapRepository
     */
    public function getCelestialObject(String $systemName, String $type, String $objectName);

    /**
     * https://robertsspaceindustries.com/api/starmap/find
     * POST Parameter: query
     * @param String $searchString
     *
     * @return StarmapRepository
     */
    public function search(String $searchString);
}