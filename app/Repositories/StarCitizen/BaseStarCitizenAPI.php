<?php declare(strict_types = 1);
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
        $this->guzzleClient = new Client(
            [
                'base_uri' => $this::API_URL,
                'timeout'  => 3.0,
                'headers'  => ['X-Rsi-Token' => $this->rsiToken],
            ]
        );

        if (is_null($this->rsiToken)) {
            $this->getRSIToken();
        }
    }

    /**
     * Requests a RSI-Token, uses Crowdfunding Stats Endpoint
     *
     * @return void
     */
    private function getRSIToken(): void
    {

        try {
            $response = $this->guzzleClient->request(
                'POST',
                'stats/getCrowdfundStats'
            );
            $token = $response->getHeader('Set-Cookie');

            if (empty($token)) {
                app('Log')::notice('Getting RSI Token failed');
                $this->rsiToken = 'StarCitizenWiki_DE';
            } else {
                $token = explode(';', $token[0])[0];
                $token = str_replace('Rsi-Token=', '', $token);
                app('Log')::info(
                    'Getting RSI Token succeeded',
                    [
                        'token' => $token,
                    ]
                );
                $this->rsiToken = $token;
            }

            if (App::isLocal()) {
                $this->createFractalInstance();
                $this->fractalManager->addMeta(['RSI-Token' => $token]);
            }

            $this->__construct();
        } catch (\Exception $e) {
            app('Log')::warning("Guzzle Request failed with Message: {$e->getMessage()}");
        }
    }

    /**
     * JSON aus Interfaces enthält (bis jetzt) immer ein success field
     *
     * @return bool
     */
    private function checkIfResponseDataIsValid(): bool
    {
        $valid = str_contains((string) $this->response->getBody(), 'success');

        if (!$valid) {
            return false;
        }

        return true;
    }
}