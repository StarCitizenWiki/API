<?php
/**
 * User: Hannes
 * Date: 19.01.2017
 * Time: 12:49
 */

namespace App\Repositories\StarCitizen\Interfaces;

/**
 * Interface CommunityInterface
 *
 * @package App\Repositories\StarCitizen\APIv1\Community
 */
interface CommunityInterface
{
    /**
     * https://robertsspaceindustries.com/api/community/getTrackedPosts
     *
     * @return \GuzzleHttp\Psr7\Response
     */
    public function getTrackedPosts();

    /**
     * https://robertsspaceindustries.com/api/community/getCitizenSpotlights
     *
     * @return \GuzzleHttp\Psr7\Response
     */
    public function getCitizenSpotlights();

    /**
     * https://robertsspaceindustries.com/api/community/getDeepSpaceRadar
     *
     * @return \GuzzleHttp\Psr7\Response
     */
    public function getDeepSpaceRadar();

    /**
     * https://robertsspaceindustries.com/api/community/getLiveStreamers
     *
     * @return \GuzzleHttp\Psr7\Response
     */
    public function getLiveStreamers();
}
