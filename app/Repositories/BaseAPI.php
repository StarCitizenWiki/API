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
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Spatie\Fractal\Fractal;

trait BaseAPI
{
    /** @var Client */
	private $_guzzleClient;
	/** @var  Response */
	protected $_response;
    /** @var array */
    protected $_responseBody;
    /** @var Fractal */
    protected $_fractal;
	/** @var  BaseAPITransformerInterface */
	protected $_transformer;
	/** Transformation Type */
	protected $_transformationType = TRANSFORM_ITEM;

	private $_allowedTransformations = [
	    TRANSFORM_COLLECTION,
        TRANSFORM_ITEM
    ];

	public function __construct()
	{
		$this->_guzzleClient = new Client(['base_uri' => $this::API_URL, 'timeout' => 3.0]);
	}

    /**
     * @param String $requestMethod
     * @param String $uri
     * @param array|null $data
     * @return Response
     * @throws InvalidDataException
     */
	public function request(String $requestMethod, String $uri, array $data = null) : Response
	{
		$this->_response = $this->_guzzleClient->request($requestMethod, $uri, $data);
        $this->_validateAndSaveResponse();
		return $this->_response;
	}

    /**
     * @return Fractal
     * @throws MissingTransformerException
     */
	public function getResponse() : Fractal
	{
        $this->_createFractalInstanceIfNotExist();

	    if (is_null($this->_transformer)) {
	        throw new MissingTransformerException();
        }
        /** TODO: viel zu hacky */
        if (is_null($this->_responseBody) || empty($this->_responseBody)) {
            $transformedResponse = $this->_fractal->data('NullResource', null, new $this->_transformer());
        } else {
            if ($this->_transformationType === TRANSFORM_COLLECTION) {
                $transformedResponse = $this->_fractal->collection($this->_responseBody, new $this->_transformer());
            } else {
                $transformedResponse = $this->_fractal->item($this->_responseBody, new $this->_transformer());
            }
        }
        $this->_addMeta();
        $this->_checkIfResponseIsValid();

		return $transformedResponse;
	}

    /**
     * @return String
     */
	public function asJSON() : String
	{
		return $this->getResponse()->toJson();
	}

    /**
     * @return array
     */
	public function asArray() : array
	{
		return $this->getResponse()->toArray();
	}

    /**
     * Sets the transformation type to Item
     */
	public function transformAsItem() : void
    {
        $this->_transformationType = TRANSFORM_ITEM;
    }

    /**
     * sets the transformation type to collection
     */
    public function transformAsCollection() : void
    {
      $this->_transformationType = TRANSFORM_COLLECTION;
    }

    /**
     * @param BaseAPITransformerInterface $transformer
     * @return $this
     */
    public function transformWith(BaseAPITransformerInterface $transformer)
    {
        $this->_transformer = $transformer;
        return $this;
    }

    /**
     * @return bool
     * @throws InvalidDataException
     */
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

    /**
     * @return bool
     */
    private function _checkIfResponseIsNotNull() : bool
    {
        return $this->_response !== null;
    }

    /**
     * @return bool
     */
    private function _checkIfResponseIsNotEmpty() : bool
    {
        return !empty($this->_response);
    }

    /**
     * @return bool
     */
    private function _checkIfResponseStatusIsOK() : bool
    {
        return $this->_response->getStatusCode() === 200;
    }

    /**
     * @return bool
     * @throws MethodNotImplementedException
     */
    private function _checkIfResponseDataIsValid() : bool
    {
        throw new MethodNotImplementedException();
    }

    /**
     * @param $string
     * @return bool
     */
    private function _validateJSON($string) : bool
    {
        if (is_string($string)) {
            @json_decode($string);
            return (json_last_error() === JSON_ERROR_NONE);
        }
        return false;
    }

    private function _addMeta() : void
    {
        $this->_fractal->addMeta([
            'request_status' => $this->_response->getStatusCode(),
            'requested_at' => Carbon::now()
        ]);
    }

    private function _createFractalInstanceIfNotExist() : void
    {
        if (is_null($this->_fractal)) {
            $this->_fractal = Fractal::create();
        }
    }

    private function _validateAndSaveResponse() : void
    {
        $responseBody = (String) $this->_response->getBody();
        if ($this->_validateJSON($responseBody)) {
            $this->_responseBody = json_decode($responseBody, true);
        } else if (is_array($responseBody)) {
            $this->_responseBody = $responseBody;
        } else {
            throw new InvalidDataException('Response Body is invalid');
        }
    }
}