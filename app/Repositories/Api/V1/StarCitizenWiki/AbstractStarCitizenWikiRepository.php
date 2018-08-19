<?php declare(strict_types = 1);
/**
 * Created by PhpStorm.
 * User: Hannes
 * Date: 01.02.2017
 * Time: 22:58
 */

namespace App\Repositories\StarCitizenWiki;

use GuzzleHttp\Client;

/**
 * Class BaseStarCitizenWikiAPI
 */
abstract class AbstractStarCitizenWikiRepository
{
    /** @var \GuzzleHttp\Client */
    protected $client;

    /** @var \GuzzleHttp\Psr7\Response */
    protected $response;

    /**
     * BaseStarCitizenWikiAPI constructor.
     */
    public function __construct()
    {
        $this->client = new Client(
            [
                'base_uri' => config('api.wiki_url'),
                'timeout' => '2',
            ]
        );
    }
}
