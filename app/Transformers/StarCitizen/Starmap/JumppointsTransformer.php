<?php declare(strict_types = 1);

namespace App\Transformers\StarCitizen\Starmap;

/**
 * Class JumppointsTransformer
 *
 * @package App\Transformers\StarCitizen\Starmap
 */
class JumppointsTransformer extends CelestialObjectTransformer
{
    /**
     * Returns all Jumppoints of the System Data
     *
     * @param mixed $celestialObject
     *
     * @return mixed
     */
    public function transform($celestialObject)
    {
        return parent::transform($celestialObject);
    }
}
