<?php declare(strict_types = 1);
/**
 * Created by PhpStorm.
 * User: Hannes
 * Date: 01.02.2017
 * Time: 23:11
 */

namespace App\Repositories;

use App\Exceptions\InvalidDataException;
use App\Interfaces\TransformableInterface;
use App\Traits\TransformsDataTrait as TransformsData;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\App;

/**
 * Class BaseAPITrait
 *
 * @package App\Repositories
 */
abstract class AbstractBaseRepository implements TransformableInterface
{
    use TransformsData;

    /**
     * Guzzle Response
     *
     * @var  \GuzzleHttp\Psr7\Response
     */
    protected $response;

    /**
     * Guzzle Client
     *
     * @var \GuzzleHttp\Client
     */
    protected $guzzleClient;

    protected $apiUrl = '';

    /**
     * BaseAPI constructor.
     */
    public function __construct()
    {
        $this->initGuzzle();
    }

    /**
     * Wrapper for Guzzle Request Function
     * Saves the Response to $this->response
     *
     * @param string     $requestMethod Request Method
     * @param string     $uri           Request URL
     * @param array|null $data          Data
     *
     * @return \GuzzleHttp\Psr7\Response
     *
     * @throws \App\Exceptions\InvalidDataException
     */
    public function request(string $requestMethod, string $uri, array $data = null): Response
    {
        $this->response = $this->guzzleClient->request($requestMethod, $uri, $data);
        $this->checkIfResponseIsValid();
        $this->validateAndSaveResponseBody();

        return $this->response;
    }

    protected function initGuzzle()
    {
        $this->guzzleClient = new Client(
            [
                'base_uri' => $this->apiUrl,
                'timeout'  => 3.0,
            ]
        );
    }

    /**
     * Adds Metadata to transformation
     *
     * @return void
     */
    protected function addMetadataToTransformation(): void
    {
        $metaData = [
            'filterable_fields' => [],
            'processed_at'      => Carbon::now(),
        ];

        if (!is_null($this->response)) {
            $metaData['request_status_code'] = $this->response->getStatusCode();
        }

        $metaData['filterable_fields'] = $this->getTransformer()->getAvailableFields();

        $this->transformedResource->addMeta($metaData);

        if (App::isLocal() && !is_null($this->response)) {
            $this->transformedResource->addMeta(
                [
                    'dev' => [
                        'response_protocol' => $this->response->getProtocolVersion(),
                        'response_headers'  => $this->response->getHeaders(),
                    ],
                ]
            );
        }
    }

    /**
     * Checks if the Response Data is valid, must be overridden
     *
     * @return bool
     */
    abstract protected function checkIfResponseDataIsValid(): bool;

    /**
     * Checks if the response is valid
     *
     * @return bool
     *
     * @throws \App\Exceptions\InvalidDataException
     */
    protected function checkIfResponseIsValid(): bool
    {
        if ($this->checkIfResponseIsNotNull() &&
            $this->checkIfResponseIsNotEmpty() &&
            $this->checkIfResponseStatusIsOK() &&
            $this->checkIfResponseDataIsValid()
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
    protected function checkIfResponseIsNotNull(): bool
    {
        return !is_null($this->response);
    }

    /**
     * Checks if the Response is not empty
     *
     * @return bool
     */
    protected function checkIfResponseIsNotEmpty(): bool
    {
        return !empty($this->response);
    }

    /**
     * Checks if the Response Status is 200 (OK)
     *
     * @return bool
     */
    protected function checkIfResponseStatusIsOK(): bool
    {
        return 200 === $this->response->getStatusCode();
    }

    /**
     * Checks if the response body is a valid json response
     *
     * @throws \App\Exceptions\InvalidDataException
     *
     * @return void
     */
    protected function validateAndSaveResponseBody(): void
    {
        $responseBody = (string) $this->response->getBody();
        if ($this->validateJSON($responseBody)) {
            $this->dataToTransform = json_decode($responseBody, true);
        } elseif (is_array($responseBody)) {
            $this->dataToTransform = $responseBody;
        } else {
            app('Log')::warning('Response Body is neither json nor array');
            throw new InvalidDataException('Response Body is invalid');
        }
    }

    /**
     * Validates a String to JSON
     *
     * @param string $string String to validate
     *
     * @return bool
     */
    protected function validateJSON(string $string): bool
    {
        if (is_string($string)) {
            @json_decode($string);

            return (json_last_error() === JSON_ERROR_NONE);
        }

        return false;
    }
}
