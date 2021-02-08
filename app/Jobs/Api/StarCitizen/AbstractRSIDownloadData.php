<?php

declare(strict_types=1);

namespace App\Jobs\Api\StarCitizen;

use App\Exceptions\InvalidDataException;
use App\Jobs\AbstractBaseDownloadData as BaseDownloadData;
use JsonException;
use stdClass;

/**
 * Class AbstractRSIDownloadData
 */
abstract class AbstractRSIDownloadData extends BaseDownloadData
{
    /**
     * @param string $rawResponseBody
     *
     * @return stdClass
     *
     * @throws InvalidDataException
     */
    protected function parseResponseBody(string $rawResponseBody): stdClass
    {
        try {
            $response = json_decode($rawResponseBody, false, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            $response = (object)['success' => 0];
        }

        if (($response->success ?? 0) !== 1) {
            throw new InvalidDataException(
                sprintf('RSI data is not valid. Expected success = 1, got %d', $response->success ?? 0)
            );
        }

        return $response;
    }
}
