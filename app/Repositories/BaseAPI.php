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
	protected $_transformator;

	function __construct()
	{
		$this->_connection = new Client(['base_uri' => $this::API_URL, 'timeout' => 3.0]);
	}

	public function request(String $requestMethod, String $uri, array $data = null)
	{
		$this->_response = $this->_connection->request($requestMethod, $uri, $data);
	}

	/**
	 * @return mixed
	 */
	public function getTransformator()
	{
		return $this->_transformator;
	}

	/**
	 * @param mixed $transformator
	 */
	public function setTransformator($transformator)
	{
		$this->_transformator = $transformator;
	}

	public function getResponse()
	{
		$transformedResponse = fractal($this->_response, new $this->_transformator())->include;
		$this->_checkIfResponseIsValid();

		return $transformedResponse;
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
		return $this->getResponse()->toJson();
	}

	public function asArray() : array
	{
		return $this->getResponse()->toArray();
	}
}