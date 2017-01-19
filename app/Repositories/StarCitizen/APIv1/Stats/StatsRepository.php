<?php
/**
 * User: Hannes
 * Date: 19.01.2017
 * Time: 13:35
 */

namespace app\Repositories\StarCitizen\APIv1\Stats;

use App\Repositories\StarCitizen\APIv1\Stats\StatsInterface as StatsInterface;

class StatsRepository implements StatsInterface
{

    /**
     * https://robertsspaceindustries.com/api/stats/getCrowdfundStats
     * @return string json
     *
     */
    public function getCrowdfundStats()
    {
        // TODO: Implement getCrowdfundStats() method.
    }
}