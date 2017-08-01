<?php declare(strict_types = 1);

namespace App\Transformers\StarCitizen\Starmap;

use App\Transformers\BaseAPITransformerInterface;

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
     * @param mixed $celestialObject
     *
     * @return mixed
     */
    public function transform($celestialObject)
    {
        return parent::transform($celestialObject);
    }
}
