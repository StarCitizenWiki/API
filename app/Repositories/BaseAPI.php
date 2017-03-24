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

    /**
     * Guzzle Client
     *
     * @var Client
     */
    private $_guzzleClient;

    /**
     * Guzzle Responst
     *
     * @var  Response
     */
    protected $response;

    /**
     * BaseAPI constructor.
     */
    public function __construct()
    {
        $this->_guzzleClient = new Client(
            [
                'base_uri' => $this::API_URL,
                'timeout' => 3.0
            ]
        );
    }

    /**
     * Wrapper for Guzzle Request Function
     *
     * @param String     $requestMethod Request Method
     * @param String     $uri           Request URL
     * @param array|null $data          Data
     *
     * @return Response
     *
     * @throws InvalidDataException
     */
    public function request(String $requestMethod, String $uri, array $data = null) : Response
    {
        $this->response = $this->_guzzleClient->request($requestMethod, $uri, $data);
        $this->_checkIfResponseIsValid();
        $this->_validateAndSaveResponseBody();
        return $this->response;
    }

    /**
     * Checks if the response is valid
     *
     * @return bool
     *
     * @throws InvalidDataException
     */
    private function _checkIfResponseIsValid() : bool
    {
        if (
            $this->_checkIfResponseIsNotNull() &&
            $this->_checkIfResponseIsNotEmpty() &&
            $this->_checkIfResponseStatusIsOK() &&
            $this->_checkIfResponseDataIsValid()
        ) {
            return true;
        }
        throw new InvalidDataException('Response Data is not valid');
    }

    /**
     * Checks if the Response is not null
     *
     * @return bool
     */
    private function _checkIfResponseIsNotNull() : bool
    {
        return $this->response !== null;
    }

    /**
     * Checks if the Response is not empty
     *
     * @return bool
     */
    private function _checkIfResponseIsNotEmpty() : bool
    {
        return !empty($this->response);
    }

    /**
     * Checks if the Response Status is 200 (OK)
     *
     * @return bool
     */
    private function _checkIfResponseStatusIsOK() : bool
    {
        return $this->response->getStatusCode() === 200;
    }

    /**
     * Checks if the Response Data is valid, must be overridden
     *
     * @return bool
     *
     * @throws MethodNotImplementedException
     */
    private function _checkIfResponseDataIsValid() : bool
    {
        throw new MethodNotImplementedException();
    }

    /**
     * Validates a String to JSON
     *
     * @param String $string String to validate
     *
     * @return bool
     */
    private function _validateJSON(String $string) : bool
    {
        if (is_string($string)) {
            @json_decode($string);
            return (json_last_error() === JSON_ERROR_NONE);
        }
        return false;
    }

    /**
     * Adds Metadata to transformation
     *
     * @return void
     */
    protected function addMetadataToTransformation() : void
    {
        $this->_transformedResource->addMeta(
            [
                'request_status_code' => $this->response->getStatusCode(),
                'processed_at' => Carbon::now()
            ]
        );

        if (App::isLocal()) {
            $this->_transformedResource->addMeta(
                [
                    'dev' => [
                        'response_protocol' => $this->response->getProtocolVersion(),
                        'response_headers' => $this->response->getHeaders()
                    ]
                ]
            );
        }
    }

    /**
     * Checks if the response body is a valid json response
     *
     * @throws InvalidDataException
     *
     * @return void
     */
    private function _validateAndSaveResponseBody() : void
    {
        $responseBody = (String) $this->response->getBody();
        if ($this->_validateJSON($responseBody)) {
            $this->dataToTransform = json_decode($responseBody, true);
        } else if (is_array($responseBody)) {
            $this->dataToTransform = $responseBody;
        } else {
            throw new InvalidDataException('Response Body is invalid');
        }
    }

    /**
     * Checks if the set transformer implements the BaseAPITransformerInterface
     *
     * @throws InterfaceNotImplementedException
     *
     * @return void
     */
    protected function checkIfTransformerIsValid() : void
    {
        if (!$this->transformer instanceof BaseAPITransformerInterface) {
            throw new InterfaceNotImplementedException(
                'Transformer does not implement BaseAPITransformerInterface'
            );
        }
    }
}