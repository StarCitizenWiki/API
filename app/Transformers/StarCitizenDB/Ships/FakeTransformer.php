<?php
/**
 * User: Hannes
 * Date: 24.04.2017
 * Time: 14:27
 */

namespace App\Transformers\StarCitizenDB\Ships;

use App\Traits\FiltersDataTrait;
use App\Transformers\BaseAPITransformerInterface;
use League\Fractal\TransformerAbstract;

/**
 * Class FakeTransformer used to return the scdb splitted files content
 * @package App\Transformers\StarCitizenDB\Ships
 */
class FakeTransformer extends TransformerAbstract implements BaseAPITransformerInterface
{
    use FiltersDataTrait;

    /**
     * Transformes the given data
     *
     * @param mixed $data Data to transform
     *
     * @return mixed
     */
    public function transform($data)
    {
        unset($data['processedName']);
        unset($data['filename']);

        return $data;
    }
}
