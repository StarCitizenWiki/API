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
use App\Facades\Log;
use App\Traits\FiltersDataTrait;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\App;

/**
 * Class BaseAPITrait
 *
 * @package App\Repositories
 */
trait BaseAPITrait
{
    /**
     * Guzzle Client
     *
     * @var Client
     */
    private $guzzleClient;

    /**
     * Guzzle Response
     *
     * @var  Response
     */
    protected $response;

    /**
     * BaseAPI constructor.
     */
    public function __construct()
    {
        Log::debug('Setting Guzzle Client');
        $this->guzzleClient = new Client([
            'base_uri' => $this::API_URL,
            'timeout' => 3.0,
        ]);
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
        Log::debug('Starting Guzzle Request', [
            'uri' => $uri,
            'request_method' => $requestMethod,
            'data' => $data,
        ]);
        $this->response = $this->guzzleClient->request($requestMethod, $uri, $data);
        $this->checkIfResponseIsValid();
        $this->validateAndSaveResponseBody();

        return $this->response;
    }

    /**
     * Checks if the response is valid
     *
     * @return bool
     *
     * @throws InvalidDataException
     */
    private function checkIfResponseIsValid() : bool
    {
        Log::debug('Checking if Response is valid');
        if ($this->checkIfResponseIsNotNull() &&
            $this->checkIfResponseIsNotEmpty() &&
            $this->checkIfResponseStatusIsOK() &&
            $this->checkIfResponseDataIsValid()
        ) {
            Log::debug('Response is valid');

            return true;
        }
        Log::debug('Response is not valid');
        throw new InvalidDataException('Response Data is not valid');
    }

    /**
     * Checks if the Response is not null
     *
     * @return bool
     */
    private function checkIfResponseIsNotNull() : bool
    {
        Log::debug('Checking if Response is not null', [
            'null' => is_null($this->response),
        ]);

        return !is_null($this->response);
    }

    /**
     * Checks if the Response is not empty
     *
     * @return bool
     */
    private function checkIfResponseIsNotEmpty() : bool
    {
        Log::debug('Checking if Response is not empty', [
            'empty' => empty($this->response),
        ]);

        return !empty($this->response);
    }

    /**
     * Checks if the Response Status is 200 (OK)
     *
     * @return bool
     */
    private function checkIfResponseStatusIsOK() : bool
    {
        Log::debug('Checking if Response Status is 200', [
            'status' => $this->response->getStatusCode(),
        ]);

        return $this->response->getStatusCode() === 200;
    }

    /**
     * Checks if the Response Data is valid, must be overridden
     *
     * @return bool
     *
     * @throws MethodNotImplementedException
     */
    private function checkIfResponseDataIsValid() : bool
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
    private function validateJSON(String $string) : bool
    {
        Log::debug('Checking if Parameter is valid JSON');
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
        $metaData = [
            'filterable_fields' => [],
            'processed_at' => Carbon::now(),
        ];

        if (!is_null($this->response)) {
            $metaData['request_status_code'] = $this->response->getStatusCode();
        }

        if (in_array(FiltersDataTrait::class, class_uses($this->transformer))) {
            $metaData['filterable_fields'] = $this->transformer->getAvailableFields();
        }

        $this->transformedResource->addMeta($metaData);

        Log::debug('Adding Metadata to Transformation', [
            'data' => $metaData,
        ]);

        if (App::isLocal() && !is_null($this->response)) {
            $this->transformedResource->addMeta([
                'dev' => [
                    'response_protocol' => $this->response->getProtocolVersion(),
                    'response_headers' => $this->response->getHeaders(),
                ],
            ]);
        }
    }

    /**
     * Checks if the response body is a valid json response
     *
     * @throws InvalidDataException
     *
     * @return void
     */
    private function validateAndSaveResponseBody() : void
    {
        Log::debug('Saving Response Body');
        $responseBody = (String) $this->response->getBody();
        if ($this->validateJSON($responseBody)) {
            Log::debug('Response Body is json');
            $this->dataToTransform = json_decode($responseBody, true);
        } elseif (is_array($responseBody)) {
            Log::debug('Response Body is array');
            $this->dataToTransform = $responseBody;
        } else {
            Log::warning('Response Body is neither json nor array');
            throw new InvalidDataException('Response Body is invalid');
        }
    }
}
