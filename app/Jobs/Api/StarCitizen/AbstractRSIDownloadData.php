<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 06.08.2018
 * Time: 13:19
 */

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
     * @return \stdClass
     *
     * @throws \App\Exceptions\InvalidDataException
     */
    protected function parseResponseBody(string $rawResponseBody): stdClass
    {
        $response = json_decode($rawResponseBody);

        if ($response->success ?? 0 !== 1) {
            throw new InvalidDataException(
                sprintf('RSI data is not valid. Expected success = 1, got %i', $response->success ?? 0)
            );
        }

        return $response;
    }
}
