<?php declare(strict_types = 1);
/**
 * User: Keonie
 * Date: 13.08.2017 17:57
 */

namespace App\Jobs;

use GuzzleHttp\Client;

/**
 * Base Class for Download Data Jobs
 * Class AbstractBaseDownloadData
 */
abstract class AbstractBaseDownloadData
{
    public const RSI_TOKEN = 'STAR-CITIZEN.WIKI_DE_API_REQUEST';

    /**
     * @var \GuzzleHttp\Client
     */
    protected $client;

    /**
     * Inits the Guzzle Client
     */
    protected function initClient(): void
    {
        $this->client = new Client(
            [
                'base_uri' => config('api.rsi_url'),
                'timeout' => 60.0,
                'headers' => ['X-RSI-Token' => self::RSI_TOKEN],
            ]
        );
    }

    /**
     * Check if Data is successful, and if Data contains the check Array values in is structure
     * e.g. for check ['data, 'resultset'], data hs to contain the key 'data' with an array value,
     * which contains a key with 'resultset'
     *
     * @param array $data  Checked Array
     * @param array $check List of Keys that are checked
     *
     * @return bool true when all Elements of $check in $data and success = 1, otherwise false
     */
    protected function checkIfDataCanBeProcessed($data, $check): bool
    {
        if (is_array($data) && $data['success'] === 1) {
            return $this->checkArrayStructure($data, $check);
        }

        return false;
    }

    /**
     * Recursive Check of Array Structure
     *
     * @param array $data  Checked Array
     * @param array $check List of Keys that are checked
     *
     * @return bool true when all Elements of $check in $data, otherwise false
     */
    protected function checkArrayStructure($data, $check)
    {
        if (!empty($check) && !empty($data)) {
            if (array_key_exists($check[0], $data)) {
                $checkKey = array_shift($check);

                return $this->checkArrayStructure($data[$checkKey], $check);
            } else {
                return false;
            }
        }

        return true;
    }
}
