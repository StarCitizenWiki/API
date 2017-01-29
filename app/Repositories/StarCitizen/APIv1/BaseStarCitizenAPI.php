<?php
/**
 * User: Hannes
 * Date: 19.01.2017
 * Time: 17:12
 */

namespace App\Repositories\StarCitizen\APIv1;

use App\Exceptions\InvalidDataException;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;

abstract class BaseStarCitizenAPI
{
    const API_URL = 'https://robertsspaceindustries.com/api/';

    private $_connection;
    /** @var  Response */
    protected $_response;

    function __construct()
    {
        $this->_connection = new Client([
            'base_uri' => BaseStarCitizenAPI::API_URL,
            'timeout' => 3.0
        ]);
    }

    protected function request(String $requestMethod, String $uri, array $data = null)
    {
        $this->_response = $this->_connection->request($requestMethod, $uri, $data);
        $this->_checkIfResponseIsValid();
    }

    private function _checkIfResponseIsValid()
    {
        if ($this->_checkIfResponseIsNotNull() &&
            $this->_checkIfResponseIsNotEmpty() &&
            $this->_checkIfResponseStatusIs200() &&
            $this->_checkIfResponseDataIsValid()) {
            return true;
        } else {
            throw new InvalidDataException('Response Data is not valid');
        }
    }

    private function _checkIfResponseIsNotNull()
    {
        return $this->_response !== null;
    }

    private function _checkIfResponseIsNotEmpty()
    {
        return !empty($this->_response);
    }

    private function _checkIfResponseStatusIs200()
    {
        return $this->_response->getStatusCode() === 200;
    }

    /**
     * JSON aus API enthält (bis jetzt) immer ein success field
     * @return bool
     */
    private function _checkIfResponseDataIsValid()
    {
        $responseData = json_decode($this->_response->getBody()->getContents(), true);
        return $responseData['success'] === 1;
    }
}