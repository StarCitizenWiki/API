<?php

namespace App\Transformers;

use \League\Fractal\TransformerAbstract;
use App\Exceptions\MethodNotImplementedException;

/**
 * Class BaseAPITransformer
 *
 * @package App\Transformers
 */
abstract class BaseAPITransformer extends TransformerAbstract implements BaseAPITransformerInterface {
    /**
     * Transformes the given data
     *
     * @param mixed $data Data to transform
     *
     * @return mixed
     *
     * @throws MethodNotImplementedException
     */
    public function transform($data)
    {
        throw new MethodNotImplementedException(
            'transform function not implemented!'
        );
    }
}