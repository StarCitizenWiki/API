<?php
/**
 * User: Hannes
 * Date: 19.01.2017
 * Time: 17:12
 */

namespace App\Repositories\StarCitizen\APIv1;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class BaseStarCitizenAPI
{
    protected $_connection;
    const API_URL = 'https://robertsspaceindustries.com/api/';

    function __construct()
    {

        $this->_connection = new Client([
            'base_uri' => BaseStarCitizenAPI::API_URL,
            'timeout' => 3.0
        ]);
    }
}