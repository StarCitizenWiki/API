<?php declare(strict_types = 1);
/**
 * Created by PhpStorm.
 * User: Hannes
 * Date: 01.02.2017
 * Time: 22:58
 */

namespace App\Repositories\StarCitizenWiki;

use App\Repositories\AbstractBaseRepository;
use GuzzleHttp\Client;

/**
 * Class BaseStarCitizenWikiAPI
 */
abstract class AbstractStarCitizenWikiRepository extends AbstractBaseRepository
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

        parent::__construct();
    }

    /**
     * JSON aus Interfaces enthält (bis jetzt) immer ein success field
     *
     * @return bool
     */
    protected function checkIfResponseDataIsValid(): bool
    {
        if (!empty($this->response->getHeader('MediaWiki-Interfaces-Error'))) {
            app('Log')::warning('Response Data is not valid', ['response' => (string) $this->response->getBody()]);

            return false;
        }

        return true;
    }
}
