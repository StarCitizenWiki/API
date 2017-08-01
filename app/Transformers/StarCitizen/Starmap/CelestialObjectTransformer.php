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
class CelestialObjectTransformer extends TransformerAbstract implements BaseAPITransformerInterface
{
    const FILTER_FIELDS = ['sourcedata'];

    /**
     * Returns all Celestial Object of the System Data
     *
     * @param mixed $system System Data
     *
     * @return mixed
     */
    public function transform($celestrialObjects)
    {
        $filteredCelestialObjects = [];

        // One Array has to be in an Array of Arrays
        if (!array_key_exists(0, $celestrialObjects)) {
            $tmpCelestrialObjects = $celestrialObjects;
            $celestrialObjects = [];
            array_push($celestrialObjects, $tmpCelestrialObjects);
        }

        foreach ($celestrialObjects as $celestrialObject) {
            array_push($filteredCelestialObjects, array_filter($celestrialObject, function($key) {
                return !in_array($key, self::FILTER_FIELDS);
            }, ARRAY_FILTER_USE_KEY));
        }
        return $filteredCelestialObjects;
    }
}
