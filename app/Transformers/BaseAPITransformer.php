<?php

namespace App\Transformers;

use GuzzleHttp\Psr7\Response;
use \League\Fractal\TransformerAbstract;

abstract class BaseAPITransformer extends TransformerAbstract implements BaseAPITransformerInterface {

	protected $success = true;

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

	public function transform($data)
    {
        throw new \Exception('transform function not implemented!');
    }
}