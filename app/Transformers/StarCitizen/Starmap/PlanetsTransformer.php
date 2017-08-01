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
 * Class PlanetsTransformer
 *
 * @package App\Transformers\StarCitizen\Starmap
 */
class PlanetsTransformer extends CelestialObjectTransformer implements BaseAPITransformerInterface
{

    /**
     * Returns all Planets of the System Data
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
