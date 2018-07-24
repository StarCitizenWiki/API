<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 19.01.2017
 * Time: 12:47
 */

namespace App\Repositories\Api\V1\StarCitizen\Interfaces\Stats;

use App\Http\Resources\Api\V1\StarCitizen\Stat\Stat;

/**
 * Interface StatsInterface
 */
interface StatsRepositoryInterface
{
    /**
     * Returns all Crowdfund Stats
     * https://robertsspaceindustries.com/api/stats/getCrowdfundStats
     *
     * @return \Illuminate\Http\Resources\Json\Resource
     */
    public function getAll();

    /**
     * @return \Illuminate\Http\Resources\Json\Resource
     */
    public function getFans(): Stat;

    /**
     * @return \Illuminate\Http\Resources\Json\Resource
     */
    public function getFleet(): Stat;

    /**
     * @return \Illuminate\Http\Resources\Json\Resource
     */
    public function getFunds(): Stat;
}
