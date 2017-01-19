<?php
/**
 * User: Hannes
 * Date: 19.01.2017
 * Time: 13:31
 */

namespace app\Repositories\StarCitizen\APIv1\Comments;


interface CommentsInterface
{

    /**
     * https://robertsspaceindustries.com/api/comments/listing
     * {id: 15677, reference_id: null}
     * @param int $postID
     * @return mixed
     */
    public function getComments(Integer $postID);
}