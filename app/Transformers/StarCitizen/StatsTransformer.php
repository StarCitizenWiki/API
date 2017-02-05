<?php
/**
 * Created by IntelliJ IDEA.
 * User: Sebastian
 * Date: 04.02.2017
 * Time: 21:29
 */

namespace App\Transformers\StarCitizen;

use App\Transformers\BaseAPITransformer;
use GuzzleHttp\Psr7\Response;

class StatsTransformer extends BaseAPITransformer
{

	public function transform(Response $response)
    {
		$responseBody = (String) $response->getBody();

		// TODO Add spezific data transformation
		$responseContent = json_decode($responseBody, true);

		$this->setSuccess($responseContent['success'] === 1);
		$this->setStatusCode($response->getStatusCode());
		return $responseContent;
	}

}
