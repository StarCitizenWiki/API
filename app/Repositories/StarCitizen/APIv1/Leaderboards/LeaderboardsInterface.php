<?php
/**
 * User: Hannes
 * Date: 19.01.2017
 * Time: 12:50
 */

namespace app\Repositories\StarCitizen\APIv1\Leaderboards;


interface LeaderboardsInterface
{
    /**
     * https://robertsspaceindustries.com/api/leaderboards/getOverview
     * @return string json
     */
    public function getOverview();

    /**
     * https://robertsspaceindustries.com/api/leaderboards/getPlayerStats
     * @return string json
     */
    public function getPlayerStats();

    /**
     * https://robertsspaceindustries.com/api/leaderboards/getLeaderboard
     * @return string json
     */
    public function getLeaderboard();
}