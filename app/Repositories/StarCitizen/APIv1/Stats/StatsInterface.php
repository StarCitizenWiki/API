<?php
/**
 * User: Hannes
 * Date: 19.01.2017
 * Time: 12:47
 */

namespace app\Repositories\StarCitizen\APIv1\Stats;


interface StatsInterface
{
    /**
     * https://robertsspaceindustries.com/api/stats/getCrowdfundStats
     * @return string json
     *
     */
    public function getCrowdfundStats();
}