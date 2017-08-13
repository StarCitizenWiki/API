<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 11.03.2017
 * Time: 20:04
 */

namespace App\Transformers\StarCitizen\Starmap;

use App\Transformers\AbstractBaseTransformer;

/**
 * Class SystemTransformer
 *
 * @package App\Transformers\StarCitizen\Starmap
 */
class CelestialObjectTransformer extends AbstractBaseTransformer
{
    const FILTER_FIELDS = ['sourcedata', 'id', 'exclude', 'created_at', 'updated_at', 'info_url', 'shader_data'];
    const RENAME_KEYS = ['cig_id' => 'id', 'cig_system_id' => 'system_id', 'cig_time_modified' => 'time_modified'];
    const SUBARRAY_NODES = ['sensor', 'subtype', 'affiliation'];

    /**
     * Returns all Celestial Object of the System Data
     *
     * @param mixed $celestialObjects
     *
     * @return mixed
     */
    public function transform($celestialObjects)
    {
        // One Array has to be in an Array of Arrays
        if (!array_key_exists(0, $celestialObjects)) {
            $tmpCelestialObjects = $celestialObjects;
            $celestialObjects = [];
            array_push($celestialObjects, $tmpCelestialObjects);
        }

        foreach ($celestialObjects as &$celestialObject) {
            $celestialObject = $this->moveToSubarray($celestialObject, static::SUBARRAY_NODES);
            $celestialObject = $this->filterAndRenameFields($celestialObject);
        }
        return $celestialObjects;
    }
}
