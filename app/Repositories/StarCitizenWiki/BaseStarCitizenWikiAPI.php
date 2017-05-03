<?php
/**
 * Created by PhpStorm.
 * User: Hanne
 * Date: 01.02.2017
 * Time: 22:58
 */

namespace App\Repositories\StarCitizenWiki;

use App\Repositories\BaseAPITrait;
use App\Traits\TransformesDataTrait;
use Illuminate\Support\Facades\Log;

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
        $this->logger::debug('Checking if Response Data is valid');
        if (!empty($this->response->getHeader('MediaWiki-Interfaces-Error'))) {
            $this->logger::warning('Response Data is not valid', [
                'response' => (String) $this->response->getBody(),
            ]);

            return false;
        }

        $this->logger::debug('Response Data is valid');

        return true;
    }
}
