<?php declare(strict_types = 1);
/**
 * Created by PhpStorm.
 * User: Hannes
 * Date: 01.02.2017
 * Time: 22:58
 */

namespace App\Repositories\StarCitizenWiki;

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
    protected function checkIfResponseDataIsValid(): bool
    {
        if (!empty($this->response->getHeader('MediaWiki-Interfaces-Error'))) {
            app('Log')::warning('Response Data is not valid', ['response' => (string) $this->response->getBody()]);

            return false;
        }

        return true;
    }
}
