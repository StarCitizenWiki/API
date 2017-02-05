<?php

namespace App\Transformers;

use GuzzleHttp\Psr7\Response;
use \League\Fractal\TransformerAbstract;

abstract class BaseAPITransformer extends TransformerAbstract implements BaseAPITransformerInterface {

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
	public function setStatusCode(int $statusCode) : void
	{
		$this->statusCode = $statusCode;
	}

	/**
	 * @return boolean
	 */
	public function isSuccess(): bool
	{
		return $this->success === true;
	}

	/**
	 * @param boolean $success
	 */
	public function setSuccess(bool $success) : void
	{
		$this->success = $success;
	}

	public function transform(Response $response)
    {
        throw new \Exception('transform function not implemented!');
    }
}