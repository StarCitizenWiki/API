<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 24.04.2017
 * Time: 14:27
 */

namespace App\Transformers;

/**
 * Class NullTransformer returns data as is
 */
class NullTransformer extends AbstractBaseTransformer
{
    /**
     * Transforms the given data
     *
     * @param mixed $data Data to transform
     *
     * @return mixed
     */
    public function transform($data)
    {
        return $data;
    }
}
