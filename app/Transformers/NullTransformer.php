<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 24.04.2017
 * Time: 14:27
 */

namespace App\Transformers;

/**
 * Class NullTransformer returns data as is
 *
 * @package App\Transformers\StarCitizenDB\Ships
 */
class NullTransformer extends AbstractBaseTransformer
{
    /**
     * Transformes the given data
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
