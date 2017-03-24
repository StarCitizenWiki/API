<?php
/**
 * Created by PhpStorm.
 * User: Hanne
 * Date: 01.02.2017
 * Time: 22:58
 */

namespace App\Repositories\StarCitizenWiki\APIv1;

use App\Exceptions\InvalidDataException;
use App\Repositories\BaseAPI;

/**
 * Class BaseStarCitizenWikiAPI
 *
 * @package App\Repositories\StarCitizenWiki\APIv1
 */
class BaseStarCitizenWikiAPI
{
    const URL = 'https://star-citizen.wiki/';
    const API_URL = BaseStarCitizenWikiAPI::URL.'api.php';

    use BaseAPI;

    /**
     * JSON aus API enthält (bis jetzt) immer ein success field
     *
     * @return bool
     */
    private function _checkIfResponseDataIsValid() : bool
    {
        if (empty($this->response->getHeader('MediaWiki-API-Error'))) {
            return true;
        }
        return false;
    }
}