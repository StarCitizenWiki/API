<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 19.01.2017
 * Time: 13:31
 */

namespace App\Repositories\StarCitizen\Interfaces;

/**
 * Interface CommentsInterface
 *
 * @package App\Repositories\StarCitizen\ApiV1\Comments
 */
interface CommentsInterface
{
    /**
     * https://robertsspaceindustries.com/api/comments/listing
     * {id: 15677, reference_id: null}
     *
     * @param int $postID
     *
     * @return \GuzzleHttp\Psr7\Response
     */
    public function getComments(Integer $postID);
}
