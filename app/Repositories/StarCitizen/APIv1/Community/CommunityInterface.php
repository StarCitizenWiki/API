<?php
/**
 * User: Hannes
 * Date: 19.01.2017
 * Time: 12:49
 */

namespace app\Repositories\StarCitizen\APIv1\Community;


interface CommunityInterface
{
    /**
     * https://robertsspaceindustries.com/api/community/getTrackedPosts
     * @return string json
     */
    public function getTrackedPosts();

    /**
     * https://robertsspaceindustries.com/api/community/getCitizenSpotlights
     * @return string json
     */
    public function getCitizenSpotlights();

    /**
     * https://robertsspaceindustries.com/api/community/getDeepSpaceRadar
     * @return string json
     */
    public function getDeepSpaceRadar();

    /**
     * https://robertsspaceindustries.com/api/community/getLiveStreamers
     * @return string json
     */
    public function getLiveStreamers();
}