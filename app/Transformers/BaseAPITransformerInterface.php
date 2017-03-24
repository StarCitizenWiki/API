<?php
/**
 * Created by PhpStorm.
 * User: Hanne
 * Date: 05.02.2017
 * Time: 22:02
 */

namespace App\Transformers;

/**
 * Interface BaseAPITransformerInterface
 *
 * @package App\Transformers
 */
interface BaseAPITransformerInterface
{
    /**
     * Transformes the given data
     *
     * @param mixed $data Data to transform
     *
     * @return mixed
     */
    public function transform($data);
}