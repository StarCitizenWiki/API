<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Traits\Jobs\CheckRsiDataStructureTrait as CheckRsiDataStructure;
use Goutte\Client as GoutteClient;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use RuntimeException;
use stdClass;
use Symfony\Component\BrowserKit\Cookie;

/**
 * Base Class for Download Data Jobs
 * Class AbstractBaseDownloadData.
 */
abstract class AbstractBaseDownloadData
{
    use CheckRsiDataStructure;

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
     * Logs a User into the RSI Website.
     *
     * @return stdClass Response JSON
     *
     * @throws RuntimeException
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
            throw new RuntimeException('Login was not successful');
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
}
