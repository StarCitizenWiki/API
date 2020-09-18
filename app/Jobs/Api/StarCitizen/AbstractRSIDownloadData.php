<?php declare(strict_types = 1);

namespace App\Jobs\Api\StarCitizen;

use App\Exceptions\InvalidDataException;
use App\Jobs\AbstractBaseDownloadData as BaseDownloadData;
use function GuzzleHttp\json_decode;
use \stdClass;

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
        $response = json_decode($rawResponseBody);

        if (($response->success ?? 0) !== 1) {
            throw new InvalidDataException(
                sprintf('RSI data is not valid. Expected success = 1, got %d', $response->success ?? 0)
            );
        }

        return $response;
    }
}
