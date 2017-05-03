<?php
/**
 * User: Hannes
 * Date: 19.01.2017
 * Time: 17:12
 */

namespace App\Repositories\StarCitizen;

use App\Repositories\BaseAPITrait;
use App\Traits\TransformesDataTrait;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\App;

/**
 * Class BaseStarCitizenAPI
 *
 * @package App\Repositories\StarCitizen\APIv1
 */
class BaseStarCitizenAPI
{
    const API_URL = 'https://robertsspaceindustries.com/api/';

    private $rsiToken = null;

    use BaseAPITrait, TransformesDataTrait {
        BaseAPITrait::addMetadataToTransformation insteadof TransformesDataTrait;
    }

    /**
     * BaseStarCitizenAPI constructor.
     */
    public function __construct()
    {
        $this->logger = App::make('Log');
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
        $this->logger->debug('Checking if Response Data is valid');

        $valid = str_contains((String) $this->response->getBody(), 'success');

        if (!$valid) {
            $this->logger->debug('Response data is not valid');

            return false;
        }

        $this->logger->debug('Response data is valid');

        return true;
    }

    /**
     * Requests a RSI-Token, uses Crowdfunding Stats Endpoint
     *
     * @return void
     */
    private function getRSIToken() : void
    {
        $this->logger->debug('Trying to get RSI Token');
        try {
            $response = $this->guzzleClient->request(
                'POST',
                'stats/getCrowdfundStats'
            );
            $token = $response->getHeader('Set-Cookie');

            if (empty($token)) {
                $this->logger->info('Getting RSI Token failed');
                $this->rsiToken = 'StarCitizenWiki_DE';
            } else {
                $token = explode(';', $token[0])[0];
                $token = str_replace('Rsi-Token=', '', $token);
                $this->logger->debug('Getting RSI Token succeeded', [
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
            $this->logger->warning('Guzzle Request failed', [
                'message' => $e->getMessage(),
            ]);
        }
    }
}
