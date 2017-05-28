<?php

namespace App\Transformers\StarCitizen\Starmap;

use App\Transformers\BaseAPITransformerInterface;
use League\Fractal\TransformerAbstract;

/**
 * Class JumppointsTransformer
 *
 * @package App\Transformers\StarCitizen\Starmap
 */
class JumppointsTransformer extends CelestialObjectTransformer implements BaseAPITransformerInterface
{

    /**
     * Returns all Jumppoints of the System Data
     *
     * @param mixed $system System Data
     *
     * @return mixed
     */
    public function transform($celestrialObject)
    {
        return parent::transform($celestrialObject);
    }
}
