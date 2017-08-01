<?php declare(strict_types = 1);
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
     * @param mixed $celestialObjects
     *
     * @return mixed
     */
    public function transform($celestialObjects)
    {
        $filteredCelestialObjects = [];

        // One Array has to be in an Array of Arrays
        if (!array_key_exists(0, $celestialObjects)) {
            $tmpCelestrialObjects = $celestialObjects;
            $celestialObjects = [];
            array_push($celestialObjects, $tmpCelestrialObjects);
        }

        foreach ($celestialObjects as $celestialObject) {
            array_push(
                $filteredCelestialObjects,
                array_filter(
                    $celestialObject,
                    function ($key) {
                        return !in_array($key, self::FILTER_FIELDS);
                    },
                    ARRAY_FILTER_USE_KEY
                )
            );
        }

        return $filteredCelestialObjects;
    }
}
