<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\StarCitizen\Starmap;

use App\Models\Api\StarCitizen\Starmap\CelestialObject\CelestialObject;
use App\Transformers\Api\V1\StarCitizen\AbstractTranslationTransformer;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;

/**
 * Class CelestialObjectTransformer
 */
class CelestialObjectTransformer extends AbstractTranslationTransformer
{
    protected $availableIncludes = [
        'starsystem',
    ];

    protected $defaultIncludes = [
        'affiliation',
        'subtype',
    ];

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
            'celestial_object_api_url' => $this->makeApiUrl(
                self::STARMAP_CELESTIAL_OBJECTS_SHOW,
                $celestialObject->code
            ),
            'name' => $celestialObject->name,
            'type' => $celestialObject->type,

            'age' => $celestialObject->age,
            'habitable' => $celestialObject->habitable,
            'fairchanceact' => $celestialObject->fairchanceact,

            'appearance' => $celestialObject->appearance,
            'designation' => $celestialObject->designation,
            'distance' => $celestialObject->distance,
            'latitude' => $celestialObject->latitude,
            'longitude' => $celestialObject->longitude,
            'axial_tilt' => $celestialObject->axial_tilt,
            'orbit_period' => $celestialObject->orbit_period,

            'info_url' => $celestialObject->info_url,

            'description' => $this->getTranslation($celestialObject),

            'sensor' => [
                'population' => $celestialObject->sensor_population,
                'economy' => $celestialObject->sensor_economy,
                'danger' => $celestialObject->sensor_danger,
            ],

            'size' => $celestialObject->size,

            'parent_id' => $celestialObject->parent_id,

            'time_modified' => $celestialObject->time_modified,
        ];
    }

    /**
     * Celestial Object affiliation, included by default
     *
     * @param CelestialObject $celestialObject
     *
     * @return Collection
     */
    public function includeAffiliation(CelestialObject $celestialObject): Collection
    {
        return $this->collection($celestialObject->affiliation, new AffiliationTransformer(), 'affiliation');
    }

    /**
     * Celestial object subtype, included by default
     *
     * @param CelestialObject $celestialObject
     *
     * @return Item
     */
    public function includeSubtype(CelestialObject $celestialObject): Item
    {
        return $this->item($celestialObject->subtype, new SubtypeTransformer(), 'subtype');
    }

    /**
     * The objects star system
     *
     * @param CelestialObject $celestialObject
     *
     * @return Item
     */
    public function includeStarsystem(CelestialObject $celestialObject): Item
    {
        return $this->item($celestialObject->starsystem, $this->makeTransformer(StarsystemTransformer::class, $this), 'starsystem');
    }
}
