<?php
/**
 * User: Hannes
 * Date: 19.01.2017
 * Time: 17:12
 */

namespace App\Repositories\StarCitizen\APIv1;

use App\Exceptions\InvalidDataException;
use App\Repositories\BaseAPI;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

class BaseStarCitizenAPI
{
    const API_URL = 'https://robertsspaceindustries.com/api/';

    private $_RSIToken = null;

    use BaseAPI;

    function __construct()
    {
        $this->_guzzleClient = new Client([
            'base_uri' => $this::API_URL,
            'timeout' => 3.0,
            'headers' => ['X-Rsi-Token' => $this->_RSIToken]
        ]);
        if (is_null($this->_RSIToken)) {
            $this->_getRSIToken();
        }
    }

    /**
     * JSON aus API enthält (bis jetzt) immer ein success field
     * @return bool
     */
    private function _checkIfResponseDataIsValid() : bool
    {
        if (strpos((String) $this->_response->getBody(), 'success') !== false) {
            return true;
        }
        return false;
    }

    /**
     * Requests a RSI-Token, uses Crowdfunding Stats Endpoint
     */
    private function _getRSIToken() : void
    {
        try {
            $response = $this->_guzzleClient->request('POST', 'stats/getCrowdfundStats');
            $token = $response->getHeader('Set-Cookie');

            if (empty($token)) {
                $this->_RSIToken = 'StarCitizenWiki_DE';
            } else {
                $token = explode(';', $token[0])[0];
                $token = str_replace('Rsi-Token=', '', $token);
                $this->_RSIToken = $token;
            }

            if (App::isLocal()) {
                $this->_createFractalInstance();
                $this->_fractal->addMeta(['RSI-Token' => $token]);
            }

            $this->__construct();
        } catch (\Exception $e) {
            Log::warning('Guzzle Request failed', [
                'message' => $e->getMessage()
            ]);
        }
    }

}