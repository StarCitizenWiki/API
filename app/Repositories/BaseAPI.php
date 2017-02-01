<?php
/**
 * Created by PhpStorm.
 * User: Hanne
 * Date: 01.02.2017
 * Time: 23:11
 */

namespace App\Repositories;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;

trait BaseAPI
{
    private $_connection;
    /** @var  Response */
    private $_response;

    function __construct()
    {
        $this->_connection = new Client([
            'base_uri' => $this::API_URL,
            'timeout' => 3.0
        ]);
    }

    public function request(String $requestMethod, String $uri, array $data = null)
    {
        $this->_response = $this->_connection->request($requestMethod, $uri, $data);
    }

    public function getResponse()
    {
        $this->_checkIfResponseIsValid();
        return $this->_response;
    }
}