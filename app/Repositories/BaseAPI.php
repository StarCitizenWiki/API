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
use Illuminate\Support\Facades\Route;

trait BaseAPI
{
    private $_connection;
    /** @var  Response */
    private $_response;
	protected $_transformator;

    function __construct()
    {
	    $this->_transformator = Route::getCurrentRoute()->getParameter('transformator');;
        $this->_connection = new Client([
            'base_uri' => $this::API_URL,
            'timeout' => 3.0
        ]);
    }

	protected function _saveResponse()
	{
		$this->_response = $this->_api->getResponse();
	}

    public function request(String $requestMethod, String $uri, array $data = null)
    {
        $this->_response = $this->_connection->request($requestMethod, $uri, $data);
    }

    public function getResponse()
    {
        $this->_checkIfResponseIsValid();
	    return fractal($this->_response, new $this->_transformator());
    }

    /*
     * Alias to getResponse
     * TODO check if neccessary
     */
	public function asResponse() : Response
	{
		return getResponse();
	}

	public function asJSON() : String
	{
		$this->_checkIfResponseIsValid();
		return $this->getResponse()->toJson();
	}

	public function asArray() : array
	{
		$this->_checkIfResponseIsValid();
		return $this->getResponse()->toArray();
	}
}