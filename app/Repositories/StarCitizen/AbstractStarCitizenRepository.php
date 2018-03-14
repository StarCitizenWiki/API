<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 19.01.2017
 * Time: 17:12
 */

namespace App\Repositories\StarCitizen;

use App\Repositories\AbstractBaseRepository as BaseRepository;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;

/**
 * Class BaseStarCitizenAPI
 */
abstract class AbstractStarCitizenRepository extends BaseRepository
{
    public const RSI_TOKEN = 'STAR-CITIZEN.WIKI_DE';

    /**
     * BaseStarCitizenAPI constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->client = new Client(
            [
                'base_uri' => config('api.rsi_url'),
                'timeout' => 3.0,
                'headers' => ['X-Rsi-Token' => self::RSI_TOKEN],
            ]
        );
    }

    /**
     * JSON aus Interfaces enthält (bis jetzt) immer ein success field
     *
     * @param \GuzzleHttp\Psr7\Response $response
     *
     * @return bool
     */
    protected function checkIfResponseDataIsValid(Response $response): bool
    {
        return str_contains((string) $response->getBody(), 'success');
    }
}
