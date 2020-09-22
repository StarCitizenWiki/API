<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\StarCitizen\Starmap;

use App\Models\Api\StarCitizen\Starmap\CelestialObject\CelestialObject;
use App\Transformers\Api\V1\StarCitizen\AbstractTranslationTransformer;

/**
 * Class CelestialObjectTransformer
 */
class CelestialObjectTransformer extends AbstractTranslationTransformer
{

    /**
     * @param CelestialObject $celestialObject
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
                'name' => $celestialObject->celestial_object_subtype->name ?? '',
                'type' => $celestialObject->celestial_object_subtype->type ?? '',
            ],
            'affiliation' => [
                'name' => $celestialObject->affiliation->name ?? '',
                'code' => $celestialObject->affiliation->code ?? '',
                'color' => $celestialObject->affiliation->color ?? '',
            ],
        ];
    }
}
