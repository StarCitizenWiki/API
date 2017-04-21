<?php
/**
 * User: Hannes
 * Date: 19.01.2017
 * Time: 17:12
 */

namespace App\Repositories\StarCitizen;

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

    private $rsiToken = null;

    use BaseAPITrait;

    /**
     * BaseStarCitizenAPI constructor.
     */
    public function __construct()
    {
        $this->guzzleClient = new Client([
            'base_uri' => $this::API_URL,
            'timeout' => 3.0,
            'headers' => ['X-Rsi-Token' => $this->rsiToken],
        ]);

        if (is_null($this->rsiToken)) {
            $this->getRSIToken();
        }
    }

    /**
     * JSON aus Interfaces enthält (bis jetzt) immer ein success field
     *
     * @return bool
     */
    private function checkIfResponseDataIsValid() : bool
    {
        Log::debug('Checking if Response Data is valid', [
            'method' => __METHOD__,
        ]);

        $valid = str_contains((String) $this->response->getBody(), 'success');

        if (!$valid) {
            Log::debug('Response data is not valid', [
                'method' => __METHOD__,
            ]);

            return false;
        }

        Log::debug('Response data is valid', [
            'method' => __METHOD__,
        ]);

        return true;
    }

    /**
     * Requests a RSI-Token, uses Crowdfunding Stats Endpoint
     *
     * @return void
     */
    private function getRSIToken() : void
    {
        Log::debug('Trying to get RSI Token', [
            'method' => __METHOD__,
        ]);
        try {
            $response = $this->guzzleClient->request(
                'POST',
                'stats/getCrowdfundStats'
            );
            $token = $response->getHeader('Set-Cookie');

            if (empty($token)) {
                Log::info('Getting RSI Token failed', [
                    'method' => __METHOD__,
                ]);
                $this->rsiToken = 'StarCitizenWiki_DE';
            } else {
                $token = explode(';', $token[0])[0];
                $token = str_replace('Rsi-Token=', '', $token);
                Log::debug('Getting RSI Token succeeded', [
                    'method' => __METHOD__,
                    'token' => $token,
                ]);
                $this->rsiToken = $token;
            }

            if (App::isLocal()) {
                $this->createFractalInstance();
                $this->fractalManager->addMeta(['RSI-Token' => $token]);
            }

            $this->__construct();
        } catch (\Exception $e) {
            Log::warning('Guzzle Request failed', [
                'method' => __METHOD__,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
