<?php
/**
 * User: Hannes
 * Date: 04.03.2017
 * Time: 12:28
 */

namespace App\Transformers\StarCitizenWiki;

use App\Transformers\BaseAPITransformer;
use GuzzleHttp\Psr7\Response;

class ShipsTransformer extends BaseAPITransformer
{
    public function transform(Response $response)
    {
        $responseBody = (String) $response->getBody();

        // TODO Add spezific data transformation
        $responseContent = json_decode($responseBody, true);

        $this->setSuccess(!empty($responseContent['query']));
        $this->setStatusCode($response->getStatusCode());
        return $responseContent;
    }
}