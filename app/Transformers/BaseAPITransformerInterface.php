<?php declare(strict_types = 1);
/**
 * Created by PhpStorm.
 * User: Hannes
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
