<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\StarCitizen\Starmap;

use App\Models\StarCitizen\Starmap\CelestialObject\CelestialObject;
use App\Transformers\Api\V1\StarCitizen\AbstractTranslationTransformer;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'celestial_object',
    title: 'Celestial Object',
    properties: [
        new OA\Property(property: 'id', type: 'integer'),
        new OA\Property(property: 'code', type: 'string'),
        new OA\Property(property: 'system_id', type: 'integer'),
        new OA\Property(property: 'celestial_object_api_url', type: 'string'),
        new OA\Property(property: 'name', type: 'string'),
        new OA\Property(property: 'type', type: 'string'),
        new OA\Property(property: 'age', type: 'integer'),
        new OA\Property(property: 'habitable', type: 'boolean'),
        new OA\Property(property: 'fairchanceact', type: 'boolean'),
        new OA\Property(property: 'appearance', type: 'string'),
        new OA\Property(property: 'designation', type: 'string'),
        new OA\Property(property: 'distance', type: 'float'),
        new OA\Property(property: 'latitude', type: 'float'),
        new OA\Property(property: 'longitude', type: 'float'),
        new OA\Property(property: 'axial_tilt', type: 'float'),
        new OA\Property(property: 'orbit_period', type: 'float'),
        new OA\Property(property: 'info_url', type: 'string'),
        new OA\Property(property: 'description', type: 'object'),
        new OA\Property(
            property: 'sensor',
            properties: [
                new OA\Property(property: 'population', type: 'float'),
                new OA\Property(property: 'economy', type: 'float'),
                new OA\Property(property: 'danger', type: 'float'),
            ],
            type: 'object'
        ),
        new OA\Property(property: 'size', type: 'float'),
        new OA\Property(property: 'parent_id', type: 'integer'),
        new OA\Property(property: 'time_modified', type: 'timestamp'),
    ],
    type: 'object'
)]
class CelestialObjectTransformer extends AbstractTranslationTransformer
{
    protected array $availableIncludes = [
        'starsystem',
        'jumppoint',
    ];

    protected array $defaultIncludes = [
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
        return $this->item(
            $celestialObject->starsystem,
            $this->makeTransformer(StarsystemTransformer::class, $this),
            'starsystem'
        );
    }

    /**
     * @param CelestialObject $celestialObject
     *
     * @return Item|void
     */
    public function includeJumppoint(CelestialObject $celestialObject)
    {
        if ($celestialObject->jumppoint() !== null) {
            return $this->item($celestialObject->jumppoint(), new JumppointTransformer());
        }
    }
}
