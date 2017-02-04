<?php

namespace App\Repositories\StarCitizen\APIv1\Common;

use \League\Fractal\TransformerAbstract;

abstract class BaseApiTransformator extends TransformerAbstract {

	protected $statusCode = 200;
	/**
	 * @return int
	 */
	public function getStatusCode(): int
	{
		return $this->statusCode;
	}

	/**
	 * @param int $statusCode
	 */
	public function setStatusCode( int $statusCode )
	{
		$this->statusCode = $statusCode;
	}

}