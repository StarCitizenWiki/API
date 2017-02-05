<?php
/**
 * Created by IntelliJ IDEA.
 * User: Sebastian
 * Date: 04.02.2017
 * Time: 21:29
 */

namespace App\Repositories\StarCitizen\APIv1\Stats;

use App\Repositories\StarCitizen\APIv1\Common\BaseAPITransformator;

class StatsTransformator extends BaseAPITransformator {

	public function transform($response) {
		$responseBody = (String) $response->getBody();

		// TODO Add spezific data transformation
		$responseContent = json_decode($responseBody, true);

		$this->setSuccess($responseContent['success'] === 1);
		$this->setStatusCode($response->getStatusCode());
		return $responseContent;
	}


}