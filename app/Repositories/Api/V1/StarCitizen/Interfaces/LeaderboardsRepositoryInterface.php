<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 19.01.2017
 * Time: 12:50
 */

namespace App\Repositories\StarCitizen\Interfaces;

/**
 * Interface LeaderboardsInterface
 */
interface LeaderboardsRepositoryInterface
{
    /**
     * https://robertsspaceindustries.com/api/leaderboards/getOverview
     *
     * @return \GuzzleHttp\Psr7\Response
     */
    public function getOverview();

    /**
     * https://robertsspaceindustries.com/api/leaderboards/getPlayerStats
     *
     * @return \GuzzleHttp\Psr7\Response
     */
    public function getPlayerStats();

    /**
     * https://robertsspaceindustries.com/api/leaderboards/getLeaderboard
     *
     * @return \GuzzleHttp\Psr7\Response
     */
    public function getLeaderboard();
}
