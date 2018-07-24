<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 19.01.2017
 * Time: 12:47
 */

namespace App\Repositories\Api\V1\StarCitizen\Interfaces\Stat;

/**
 * Interface StatsInterface
 */
interface StatRepositoryInterface
{
    /**
     * Returns all Crowdfund Stats
     * https://robertsspaceindustries.com/api/stats/getCrowdfundStats
     *
     * @return \Dingo\Api\Http\Response
     */
    public function getAll();

    /**
     * Returns latest Crowdfund Stats
     * https://robertsspaceindustries.com/api/stats/getCrowdfundStats
     *
     * @return \Dingo\Api\Http\Response
     */
    public function getLatest();
}
