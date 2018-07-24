<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 19.01.2017
 * Time: 12:47
 */

namespace App\Repositories\StarCitizen\Interfaces;

/**
 * Interface StatsInterface
 */
interface FundsRepositoryInterface
{
    /**
     * Returns current RSI Funds
     *
     * @return \Spatie\Fractal\Fractal
     */
    public function getFunds();
}
