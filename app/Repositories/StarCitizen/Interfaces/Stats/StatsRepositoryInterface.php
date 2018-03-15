<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 19.01.2017
 * Time: 12:47
 */

namespace App\Repositories\StarCitizen\Interfaces\Stats;

/**
 * Interface StatsInterface
 */
interface StatsRepositoryInterface
{
    /**
     * Returns all Crowdfund Stats
     * https://robertsspaceindustries.com/api/stats/getCrowdfundStats
     */
    public function getAll();

    public function getFans();

    public function getFleet();

    public function getFunds();
}
