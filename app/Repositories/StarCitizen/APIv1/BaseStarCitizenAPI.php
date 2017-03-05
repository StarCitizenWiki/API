<?php
/**
 * User: Hannes
 * Date: 19.01.2017
 * Time: 17:12
 */

namespace App\Repositories\StarCitizen\APIv1;

use App\Exceptions\InvalidDataException;
use App\Repositories\BaseAPI;
use GuzzleHttp\Client;

class BaseStarCitizenAPI
{
    const API_URL = 'https://robertsspaceindustries.com/api/';

    use BaseAPI;

    function __construct()
    {
        $this->_connection = new Client([
            'base_uri' => $this::API_URL,
            'timeout' => 3.0,
            'headers' => ['X-Rsi-Token' => null]
        ]);
    }

    /**
     * JSON aus API enthält (bis jetzt) immer ein success field
     * @return bool
     */
    private function _checkIfResponseDataIsValid() : bool
    {
		return $this->_transformer->isSuccess();
    }
}