<?php
/**
 * Created by PhpStorm.
 * User: Hanne
 * Date: 01.02.2017
 * Time: 22:58
 */

namespace App\Repositories\StarCitizenWiki;

use App\Facades\Log;
use App\Repositories\BaseAPITrait;
use App\Traits\TransformesDataTrait;

/**
 * Class BaseStarCitizenWikiAPI
 *
 * @package App\Repositories\StarCitizenWiki\APIv1
 */
class BaseStarCitizenWikiAPI
{
    const URL = 'https://star-citizen.wiki/';
    const API_URL = BaseStarCitizenWikiAPI::URL.'api.php';

    use BaseAPITrait, TransformesDataTrait {
        BaseAPITrait::addMetadataToTransformation insteadof TransformesDataTrait;
    }

    /**
     * JSON aus Interfaces enthält (bis jetzt) immer ein success field
     *
     * @return bool
     */
    private function checkIfResponseDataIsValid() : bool
    {
        if (!empty($this->response->getHeader('MediaWiki-Interfaces-Error'))) {
            app('Log')::warning('Response Data is not valid', ['response' => (String) $this->response->getBody()]);

            return false;
        }

        return true;
    }
}
