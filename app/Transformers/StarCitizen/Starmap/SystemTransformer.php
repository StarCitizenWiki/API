<?php
/**
 * User: Hannes
 * Date: 11.03.2017
 * Time: 20:04
 */

namespace App\Transformers\StarCitizen\Starmap;

use App\Transformers\BaseAPITransformerInterface;
use League\Fractal\TransformerAbstract;

/**
 * Class SystemTransformer
 *
 * @package App\Transformers\StarCitizen\Starmap
 */
class SystemTransformer extends TransformerAbstract implements BaseAPITransformerInterface
{

    /**
     * TODO
     *
     * @param mixed $system System Data
     *
     * @return mixed
     */
    public function transform($system)
    {
        return $system;
    }
}
