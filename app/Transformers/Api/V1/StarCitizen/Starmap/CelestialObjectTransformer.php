<?php
/**
 * User: Keonie
 * Date: 07.08.2018 15:20
 */

namespace App\Transformers\Api\V1\StarCitizen\Starmap;

use App\Transformers\Api\V1\StarCitizen\AbstractTranslationTransformer;
use App\Models\Api\StarCitizen\Starmap\CelestialObject\CelestialObject;

/**
 * Class CelestialObjectTransformer
 * @package App\Transformers\Api\V1\StarCitizen\Starmap
 */
class CelestialObjectTransformer extends AbstractTranslationTransformer
{

    /**
     * @param \App\Models\Api\StarCitizen\Starmap\CelestialObject\CelestialObject $celestialObject
     *
     * @return array
     */
    public function transform(CelestialObject $celestialObject)
    {
        return [
            'id' => $celestialObject->cig_id,
            'code' => $celestialObject->code,
            'system_id' => $celestialObject->cig_system_id,
            'time_modified' => $celestialObject->cig_time_modified,
            'type' => $celestialObject->type,
            'designation' => $celestialObject->designation,
            'name' => $celestialObject->name,
            'age' => $celestialObject->age,
            'distance' => $celestialObject->distance,
            'latitude' => $celestialObject->latitude,
            'longitude' => $celestialObject->longitude,
            'axial_tilt' => $celestialObject->axial_tilt,
            'orbit_period' => $celestialObject->orbit_period,
            'description' => $this->getTranslation($celestialObject->description),
            'info_url' => $celestialObject->info_url,
            'habitable' => $celestialObject->habitable,
            'fairchanceact' => $celestialObject->fairchanceact,
            'appearance' => $celestialObject->appearance,
            'sensor' => [
                'population' => $celestialObject->sensor_population,
                'economy' => $celestialObject->sensor_economy,
                'danger' => $celestialObject->sensor_danger,
            ],
            'size' => $celestialObject->size,
            'parent_id' => $celestialObject->parent_id,
            'subtype' => [
                'id' => $celestialObject->subtype->id,
                'name' => $celestialObject->subtype->name,
                'type' => $celestialObject->subtype->type,
            ],
            'affiliation' => [
                'name' => $celestialObject->subtype->name,
                'code' => $celestialObject->subtype->code,
                'color' => $celestialObject->subtype->color,
            ],
        ];
    }
}
