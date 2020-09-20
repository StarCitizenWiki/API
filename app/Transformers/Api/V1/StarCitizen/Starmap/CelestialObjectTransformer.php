<?php declare(strict_types=1);
/**
 * User: Keonie
 * Date: 07.08.2018 15:20
 */

namespace App\Transformers\Api\V1\StarCitizen\Starmap;

use App\Models\Api\StarCitizen\Starmap\CelestialObject\CelestialObject;
use App\Transformers\Api\V1\StarCitizen\AbstractTranslationTransformer;

/**
 * Class CelestialObjectTransformer
 */
class CelestialObjectTransformer extends AbstractTranslationTransformer
{

    /**
     * @param \App\Models\Api\StarCitizen\Starmap\CelestialObject\CelestialObject $celestialObject
     *
     * @return array
     */
    public function transform(CelestialObject $celestialObject): array
    {
        return [
            'id' => $celestialObject->cig_id,
            'code' => $celestialObject->code,
            'system_id' => $celestialObject->starsystem_id,
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
            'description' => $this->getTranslation($celestialObject),
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
                'name' => !empty($celestialObject->celestial_object_subtype) ?
                    $celestialObject->celestial_object_subtype->name : "",
                'type' => !empty($celestialObject->celestial_object_subtype) ?
                    $celestialObject->celestial_object_subtype->type : "",
            ],
            'affiliation' => [
                'name' => !empty($celestialObject->affiliation) ?
                    $celestialObject->affiliation->name : "",
                'code' => !empty($celestialObject->affiliation) ?
                    $celestialObject->affiliation->code : "",
                'color' => !empty($celestialObject->affiliation) ?
                    $celestialObject->affiliation->color : "",
            ],
        ];
    }
}
