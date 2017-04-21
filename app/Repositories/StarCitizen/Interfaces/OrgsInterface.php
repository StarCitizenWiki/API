<?php
/**
 * User: Hannes
 * Date: 19.01.2017
 * Time: 13:04
 */

namespace App\Repositories\StarCitizen\Interfaces;

/**
 * Interface OrgsInterface
 *
 * @package App\Repositories\StarCitizen\APIv1\Orgs
 */
interface OrgsInterface
{
    /**
     * https://robertsspaceindustries.com/api/orgs/getOrgs
     *
     * @return \GuzzleHttp\Psr7\Response
     */
    public function getOrgs();
}
