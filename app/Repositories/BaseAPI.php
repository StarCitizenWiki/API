<?php
/**
 * Created by PhpStorm.
 * User: Hanne
 * Date: 01.02.2017
 * Time: 23:11
 */

namespace App\Repositories;

use App\Exceptions\InterfaceNotImplementedException;
use App\Exceptions\InvalidDataException;
use App\Exceptions\InvalidTransformerException;
use App\Exceptions\MethodNotImplementedException;
use App\Exceptions\MissingTransformerException;
use App\Traits\TransformesData;
use App\Transformers\BaseAPITransformerInterface;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\App;
use Spatie\Fractal\Fractal;

trait BaseAPI
{
    use TransformesData;

    /** @var Client */
	private $_guzzleClient;
	/** @var  Response */
	protected $_response;

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
        $this->_checkIfResponseIsValid();
        $this->_validateAndSaveResponseBody();
		return $this->_response;
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

    /**
     * Adds Metadata to transformation
     */
    protected function _addMetadataToTransformation() : void
    {
        $this->_transformedResource->addMeta([
            'request_status_code' => $this->_response->getStatusCode(),
            'processed_at' => Carbon::now()
        ]);

        if (App::isLocal()) {
            $this->_transformedResource->addMeta([
                'dev' => [
                    'response_protocol' => $this->_response->getProtocolVersion(),
                    'response_headers' => $this->_response->getHeaders()
                ]
            ]);
        }
    }

    /**
     * checks if the response body is a valid json response
     * @throws InvalidDataException
     */
    private function _validateAndSaveResponseBody() : void
    {
        $responseBody = (String) $this->_response->getBody();
        if ($this->_validateJSON($responseBody)) {
            $this->_dataToTransform = json_decode($responseBody, true);
        } else if (is_array($responseBody)) {
            $this->_dataToTransform = $responseBody;
        } else {
            throw new InvalidDataException('Response Body is invalid');
        }
    }

    protected function _checkIfTransformerIsValid($transformer)
    {
        $transformer = new $transformer();

        if (!$transformer instanceof BaseAPITransformerInterface) {
            throw new InterfaceNotImplementedException('Transformer does not implement BaseAPITransformerInterface');
        }
    }

    protected function _setTransformationType()
    {
        if (is_null($this->_dataToTransform) || empty($this->_dataToTransform)) {
            $this->_transformationType = TRANSFORM_NULL;
        }
    }
}