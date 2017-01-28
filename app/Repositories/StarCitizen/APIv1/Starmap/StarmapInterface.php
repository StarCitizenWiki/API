<?php
/**
 * User: Hannes
 * Date: 19.01.2017
 * Time: 13:09
 */

namespace App\Repositories\StarCitizen\APIv1\Starmap;


interface StarmapInterface
{
    /**
     * https://robertsspaceindustries.com/api/starmap/star-systems/{SYSTEM}
     * @param String $systemName
     * @return \GuzzleHttp\Psr7\Response
     */
    public function getSystem(String $systemName);

    /**
     * https://robertsspaceindustries.com/api/starmap/celestial-objects/{SYSTEM_NAME}.[TYPE}.{NAME}
     * @param String $systemName
     * @param String $objectName
     * @param String $type
     * @return \GuzzleHttp\Psr7\Response
     */
    public function getCelestialObject(String $systemName, String $objectName, String $type);

    /**
     * https://robertsspaceindustries.com/api/starmap/find
     * POST Parameter: query
     * @param String $searchString
     * @return \GuzzleHttp\Psr7\Response
     */
    public function find(String $searchString);
}