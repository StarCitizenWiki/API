<?php

namespace App\Repositories\StarCitizen\APIv1\Common;

use \League\Fractal\TransformerAbstract;

abstract class BaseApiTransformator extends TransformerAbstract {

	protected $statusCode = 200;
	protected $success = true;
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

	/**
	 * @return boolean
	 */
	public function isSuccess(): bool
	{
		return $this->success;
	}

	/**
	 * @param boolean $success
	 */
	public function setSuccess(bool $success)
	{
		$this->success = $success;
	}
}