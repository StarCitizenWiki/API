<?php
/**
 * Created by PhpStorm.
 * User: Hanne
 * Date: 01.02.2017
 * Time: 23:11
 */

namespace App\Repositories;

use App\Exceptions\InvalidDataException;
use App\Exceptions\MethodNotImplementedException;
use App\Exceptions\MissingTransformerException;
use App\Transformers\BaseAPITransformerInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use League\Fractal\TransformerAbstract;
use Spatie\Fractal\Fractal;

trait BaseAPI
{
    /** @var Client */
	private $_guzzleClient;
	/** @var  Response */
	protected $_response;
    /** @var array */
    protected $_responseBody;
	/** @var  BaseAPITransformerInterface */
	protected $_transformer;
	/** Transformation Type */
	protected $_transformationType = TRANSFORM_ITEM;

	private $_allowedTransformations = [
	    TRANSFORM_COLLECTION,
        TRANSFORM_ITEM
    ];

	function __construct()
	{
		$this->_guzzleClient = new Client(['base_uri' => $this::API_URL, 'timeout' => 3.0]);
	}

	public function request(String $requestMethod, String $uri, array $data = null) : Response
	{
		$this->_response = $this->_guzzleClient->request($requestMethod, $uri, $data);
		$responseBody = (String) $this->_response->getBody();
		if ($this->_validateJSON($responseBody)) {
		    $this->_responseBody = json_decode($responseBody, true);
        } else if (is_array($responseBody)) {
		    $this->_responseBody = $responseBody;
        } else {
		    throw new InvalidDataException('Response Body is invalid');
        }
		return $this->_response;
	}

	public function getResponse() : Fractal
	{
	    if (is_null($this->_transformer)) {
	        throw new MissingTransformerException();
        }

	    if ($this->_transformationType === TRANSFORM_COLLECTION) {
            $transformedResponse = fractal($this->_responseBody, new $this->_transformer());
        } else {
	        $transformedResponse = fractal()->item($this->_responseBody, new $this->_transformer());
        }
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

	public function transformAsItem() : void
    {
        $this->_transformationType = TRANSFORM_ITEM;
    }

    public function transformAsCollection() : void
    {
      $this->_transformationType = TRANSFORM_COLLECTION;
    }

    public function transformWith(BaseAPITransformerInterface $transformer)
    {
        $this->_transformer = $transformer;
        return $this;
    }

    private function _checkIfResponseIsValid() : bool
    {
        if ($this->_checkIfResponseIsNotNull() &&
            $this->_checkIfResponseIsNotEmpty() &&
            $this->_checkIfResponseStatusIsOK() &&
            $this->_checkIfResponseDataIsValid()) {
            return true;
        } else {
            throw new InvalidDataException('Response Data is not valid');
        }
    }

    private function _checkIfResponseIsNotNull() : bool
    {
        return $this->_response !== null;
    }

    private function _checkIfResponseIsNotEmpty() : bool
    {
        return !empty($this->_response);
    }

    private function _checkIfResponseStatusIsOK() : bool
    {
        return $this->_response->getStatusCode() === 200;
    }

    private function _checkIfResponseDataIsValid() : bool
    {
        throw new MethodNotImplementedException();
    }

    private function _validateJSON($string) : bool
    {
        if (is_string($string)) {
            @json_decode($string);
            return (json_last_error() === JSON_ERROR_NONE);
        }
        return false;
    }
}