<?php
/**
 * Created by PhpStorm.
 * User: Hanne
 * Date: 01.02.2017
 * Time: 23:11
 */

namespace App\Repositories;

use App\Transformers\BaseAPITransformerInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Spatie\Fractal\Fractal;

trait BaseAPI
{
	private $_connection;
	/** @var  Response */
	private $_response;
	/** @var  BaseAPITransformerInterface */
	protected $_transformer;

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
	public function getTransformer()
	{
		return $this->_transformer;
	}

	/**
	 * @param mixed $transformer
	 */
	public function setTransformer(BaseAPITransformerInterface $transformer)
	{
		$this->_transformer = $transformer;
	}

	public function getResponse() : Fractal
	{
		$transformedResponse = fractal($this->_response, new $this->_transformer());
		$this->_checkIfResponseIsValid();

		return $transformedResponse;
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