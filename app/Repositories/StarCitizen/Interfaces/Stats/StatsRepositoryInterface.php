<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 19.01.2017
 * Time: 12:47
 */

namespace App\Repositories\StarCitizen\Interfaces\Stats;

use Spatie\Fractal\Fractal;

/**
 * Interface StatsInterface
 */
interface StatsRepositoryInterface
{
    /**
     * Returns all Crowdfund Stats
     * https://robertsspaceindustries.com/api/stats/getCrowdfundStats
     *
     * @return \Spatie\Fractal\Fractal
     */
    public function getAll(): Fractal;

    /**
     * @return \Spatie\Fractal\Fractal
     */
    public function getFans(): Fractal;

    /**
     * @return \Spatie\Fractal\Fractal
     */
    public function getFleet(): Fractal;

    /**
     * @return \Spatie\Fractal\Fractal
     */
    public function getFunds(): Fractal;
}
