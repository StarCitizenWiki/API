<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Traits\Jobs\CheckRsiDataStructureTrait as CheckRsiDataStructure;
use Symfony\Component\BrowserKit\HttpBrowser;
use GuzzleHttp\Cookie\CookieJar;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
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
     * @var PendingRequest
     */
    protected static $client;

    /**
     * @var CookieJar
     */
    protected static $cookieJar;

    /**
     * @var HttpBrowser
     */
    protected static $scraper;

    /**
     * Inits the Guzzle Client.
     *
     * @param bool $withTokenHeader
     *
     * @return PendingRequest
     */
    protected function makeClient(bool $withTokenHeader = true): PendingRequest
    {
        if (null === self::$client) {
            self::$cookieJar = new CookieJar();

            $client = Http::withOptions(
                [
                    'base_uri' => config('api.rsi_url'),
                    'cookies' => self::$cookieJar,
                ]
            )->timeout(60);

            if (true === $withTokenHeader) {
                $client = $client->withHeaders(
                    [
                        'X-RSI-Token' => self::RSI_TOKEN,
                    ]
                );
            }

            self::$client = $client;
        }

        return self::$client;
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

        $response = $res->json();

        if (1 !== $response['success'] || !$res->successful()) {
            throw new RuntimeException('Login was not successful');
        }

        return $response;
    }

    /**
     * Add Guzzle Cookies to Goutte.
     *
     * @param HttpBrowser $client
     *
     * @return HttpBrowser
     */
    protected function addGuzzleCookiesToScraper(HttpBrowser $client): HttpBrowser
    {
        foreach (self::$cookieJar->toArray() as $cookie) {
            $client->getCookieJar()->set(
                new Cookie($cookie['Name'], $cookie['Value'], null, $cookie['Path'], $cookie['Domain'])
            );
        }

        return $client;
    }
}
