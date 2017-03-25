<?php
/**
 * User: Hannes
 * Date: 04.03.2017
 * Time: 12:28
 */

namespace App\Transformers\StarCitizenWiki\Ships;

use App\Traits\FiltersDataTrait;
use App\Transformers\BaseAPITransformerInterface;
use League\Fractal\TransformerAbstract;

/**
 * Class ShipsTransformer
 *
 * @package App\Transformers\StarCitizenWiki\Ships
 */
class ShipsTransformer extends TransformerAbstract implements BaseAPITransformerInterface
{
    use FiltersDataTrait;

    /**
     * Transformes a given Ship
     *
     * @param mixed $ship Ship to transform
     *
     * @return mixed
     */
    public function transform($ship)
    {
        return $ship;
    }
}