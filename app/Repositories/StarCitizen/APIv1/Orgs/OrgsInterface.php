<?php
/**
 * User: Hannes
 * Date: 19.01.2017
 * Time: 13:04
 */

namespace app\Repositories\StarCitizen\APIv1\Orgs;


interface OrgsInterface
{
    /**
     * https://robertsspaceindustries.com/api/orgs/getOrgs
     * @return string json
     */
    public function getOrgs();
}