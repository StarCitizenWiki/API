<?php

declare(strict_types=1);

namespace App\Jobs;

use Goutte\Client as GoutteClient;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use stdClass;
use Symfony\Component\BrowserKit\Cookie;

/**
 * Base Class for Download Data Jobs
 * Class AbstractBaseDownloadData.
 */
abstract class AbstractBaseDownloadData
{
    public const RSI_TOKEN = 'STAR-CITIZEN.WIKI_DE_API_REQUEST';

    /**
     * @var Client
     */
    protected static $client;

    /**
     * @var CookieJar
     */
    protected static $cookieJar;

    /**
     * @var GoutteClient
     */
    protected static $scraper;

    /**
     * Inits the Guzzle Client.
     *
     * @param bool $withTokenHeader
     */
    protected function initClient(bool $withTokenHeader = true): void
    {
        if (null === self::$client) {
            self::$cookieJar = new CookieJar();

            $config = [
                'base_uri' => config('api.rsi_url'),
                'timeout' => 60.0,
                'cookies' => self::$cookieJar,
            ];

            if (true === $withTokenHeader) {
                $config['headers'] = [
                    'X-RSI-Token' => self::RSI_TOKEN,
                ];
            }

            self::$client = new Client($config);
        }
    }

    /**
     * Check if Data is successful, and if Data contains the check Array values in is structure
     * e.g. for check ['data, 'resultset'], data hs to contain the key 'data' with an array value,
     * which contains a key with 'resultset'.
     *
     * @param array $data  Checked Array
     * @param array $check List of Keys that are checked
     *
     * @return bool true when all Elements of $check in $data and success = 1, otherwise false
     */
    protected function checkIfDataCanBeProcessed(array $data, array $check): bool
    {
        if (is_array($data) && 1 === $data['success']) {
            return $this->checkArrayStructure($data, $check);
        }

        return false;
    }

    /**
     * Recursive Check of Array Structure.
     *
     * @param array $data  Checked Array
     * @param array $check List of Keys that are checked
     *
     * @return bool true when all Elements of $check in $data, otherwise false
     */
    protected function checkArrayStructure(array $data, array $check): bool
    {
        if (!empty($check) && !empty($data)) {
            if (array_key_exists($check[0], $data)) {
                $checkKey = array_shift($check);

                return $this->checkArrayStructure($data[$checkKey], $check);
            }

            return false;
        }

        return true;
    }

    /**
     * Logs a User into the RSI Webseite.
     *
     * @return stdClass Response JSON
     */
    protected function getRsiAuthCookie(): stdClass
    {
        $res = self::$client->post(
            'api/account/signin',
            [
                'form_params' => [
                    'username' => config('services.rsi_account.username'),
                    'password' => config('services.rsi_account.password'),
                ],
                'cookies' => self::$cookieJar,
            ]
        );

        $response = \GuzzleHttp\json_decode($res->getBody()->getContents());

        if (1 !== $response->success) {
            throw new \InvalidArgumentException('Login was not successful');
        }

        return $response;
    }

    /**
     * Add Guzzle Cookies to Goutte.
     *
     * @param GoutteClient $client
     *
     * @return GoutteClient
     */
    protected function addGuzzleCookiesToScraper(GoutteClient $client): GoutteClient
    {
        foreach (self::$cookieJar->toArray() as $cookie) {
            $client->getCookieJar()->set(
                new Cookie($cookie['Name'], $cookie['Value'], null, $cookie['Path'], $cookie['Domain'])
            );
        }

        return $client;
    }

    /**
     * Create a Scraper if it does not exist.
     *
     * @param bool $withAuth
     */
    protected function makeScraper(bool $withAuth = false): void
    {
        if (null === self::$scraper) {
            $this->initClient(false);

            self::$scraper = new GoutteClient();
            self::$scraper->setClient(self::$client);

            if ($withAuth) {
                $this->addGuzzleCookiesToScraper(self::$scraper);
            }
        }
    }
}
