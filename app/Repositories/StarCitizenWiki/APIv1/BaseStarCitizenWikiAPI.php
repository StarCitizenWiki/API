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

class BaseStarCitizenWikiAPI
{
    const URL = 'https://star-citizen.wiki/';
    const API_URL = BaseStarCitizenWikiAPI::URL.'api.php';

    use BaseAPI;
}