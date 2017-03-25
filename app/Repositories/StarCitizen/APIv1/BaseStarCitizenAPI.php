<?php
/**
 * User: Hannes
 * Date: 19.01.2017
 * Time: 17:12
 */

namespace App\Repositories\StarCitizen\APIv1;

use App\Exceptions\InvalidDataException;
use App\Repositories\BaseAPITrait;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

/**
 * Class BaseStarCitizenAPI
 *
 * @package App\Repositories\StarCitizen\APIv1
 */
class BaseStarCitizenAPI
{
    const API_URL = 'https://robertsspaceindustries.com/api/';

    private $RSIToken = null;

    use BaseAPITrait;

    /**
     * BaseStarCitizenAPI constructor.
     */
    public function __construct()
    {
        $this->guzzleClient = new Client(
            [
                'base_uri' => $this::API_URL,
                'timeout' => 3.0,
                'headers' => ['X-Rsi-Token' => $this->RSIToken],
            ]
        );
        if (is_null($this->RSIToken)) {
            $this->getRSIToken();
        }
    }

    /**
     * JSON aus API enthält (bis jetzt) immer ein success field
     *
     * @return bool
     */
    private function checkIfResponseDataIsValid() : bool
    {
        if (strpos((String) $this->response->getBody(), 'success') !== false) {
            return true;
        }

        return false;
    }

    /**
     * Requests a RSI-Token, uses Crowdfunding Stats Endpoint
     *
     * @return void
     */
    private function getRSIToken() : void
    {
        try {
            $response = $this->guzzleClient->request(
                'POST',
                'stats/getCrowdfundStats'
            );
            $token = $response->getHeader('Set-Cookie');

            if (empty($token)) {
                $this->RSIToken = 'StarCitizenWiki_DE';
            } else {
                $token = explode(';', $token[0])[0];
                $token = str_replace('Rsi-Token=', '', $token);
                $this->RSIToken = $token;
            }

            if (App::isLocal()) {
                $this->createFractalInstance();
                $this->fractalManager->addMeta(['RSI-Token' => $token]);
            }

            $this->__construct();
        } catch (\Exception $e) {
            Log::warning(
                'Guzzle Request failed',
                ['message' => $e->getMessage()]
            );
        }
    }
}
