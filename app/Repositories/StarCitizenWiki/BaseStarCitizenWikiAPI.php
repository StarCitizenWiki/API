<?php
/**
 * Created by PhpStorm.
 * User: Hanne
 * Date: 01.02.2017
 * Time: 22:58
 */

namespace App\Repositories\StarCitizenWiki;

use App\Repositories\BaseAPITrait;
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

    use BaseAPITrait;

    /**
     * JSON aus Interfaces enthält (bis jetzt) immer ein success field
     *
     * @return bool
     */
    private function checkIfResponseDataIsValid() : bool
    {
        Log::debug('Checking if Response Data is valid', [
            'method' => __METHOD__,
        ]);
        if (!empty($this->response->getHeader('MediaWiki-Interfaces-Error'))) {
            Log::warning('Response Data is not valid', [
                'method' => __METHOD__,
                'response' => (String) $this->response->getBody(),
            ]);

            return false;
        }

        Log::debug('Response Data is valid', [
            'method' => __METHOD__,
        ]);

        return true;
    }
}
