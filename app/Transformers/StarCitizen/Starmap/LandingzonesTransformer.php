<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 11.03.2017
 * Time: 20:04
 */

namespace App\Transformers\StarCitizen\Starmap;

use App\Transformers\BaseAPITransformerInterface;

/**
 * Class LandingzonesTransformer
 *
 * @package App\Transformers\StarCitizen\Starmap
 */
class LandingzonesTransformer extends CelestialObjectTransformer implements BaseAPITransformerInterface
{
    /**
     * Returns all Landingzones of the System Data
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
