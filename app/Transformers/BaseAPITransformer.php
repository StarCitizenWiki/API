<?php

namespace App\Transformers;

use \League\Fractal\TransformerAbstract;
use App\Exceptions\MethodNotImplementedException;

abstract class BaseAPITransformer extends TransformerAbstract implements BaseAPITransformerInterface {
    /**
     * @param $data
     * @throws MethodNotImplementedException
     * @return void
     */
	public function transform($data)
    {
        throw new MethodNotImplementedException('transform function not implemented!');
    }
}