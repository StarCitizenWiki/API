<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 19.01.2017
 * Time: 17:12
 */

namespace App\Repositories\StarCitizen;

use App\Repositories\AbstractBaseRepository as BaseRepository;
use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;

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
     * @param \Psr\Http\Message\ResponseInterface $response
     *
     * @return bool
     */
    protected function checkIfResponseDataIsValid(ResponseInterface $response): bool
    {
        return str_contains((string) $response->getBody(), 'success');
    }
}
