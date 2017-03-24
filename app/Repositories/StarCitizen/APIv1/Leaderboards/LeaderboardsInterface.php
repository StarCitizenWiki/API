<?php
/**
 * User: Hannes
 * Date: 19.01.2017
 * Time: 12:50
 */

namespace App\Repositories\StarCitizen\APIv1\Leaderboards;

/**
 * Interface LeaderboardsInterface
 *
 * @package App\Repositories\StarCitizen\APIv1\Leaderboards
 */
interface LeaderboardsInterface
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