<?php

declare(strict_types=1);

namespace App\Http\Resources\StarCitizen\Starmap;

use App\Http\Resources\AbstractTranslationResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'celestial_object_v2',
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
        new OA\Property(property: 'time_modified', type: 'string'),
    ],
    type: 'object'
)]
class CelestialObjectResource extends AbstractTranslationResource
{
    public static function validIncludes(): array
    {
        return [
            'affiliation',
            'starsystem',
        ];
    }

    public function toArray($request): array
    {
        return [
            'id' => $this->cig_id,
            'code' => $this->code,
            'system_id' => $this->starsystem_id,
            'link' => $this->makeApiUrl(
                self::STARMAP_CELESTIAL_OBJECTS_SHOW,
                $this->code
            ),
            'name' => $this->name,
            'type' => $this->type,

            'age' => $this->age,
            'habitable' => $this->habitable,
            'fairchanceact' => $this->fairchanceact,

            'appearance' => $this->appearance,
            'designation' => $this->designation,
            'distance' => $this->distance,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'axial_tilt' => $this->axial_tilt,
            'orbit_period' => $this->orbit_period,

            'info_url' => $this->info_url,

            'description' => $this->getTranslation($this, $request),

            'sensor' => [
                'population' => $this->sensor_population,
                'economy' => $this->sensor_economy,
                'danger' => $this->sensor_danger,
            ],

            'size' => $this->size,

            'parent_id' => $this->parent_id,

            'affiliation' => AffiliationResource::collection($this->whenLoaded('affiliation')),
            'starsystem' => new StarsystemResource($this->whenLoaded('starsystem')),
            $this->mergeWhen($this->whenLoaded('subtype'), [
                'sub_type' => [
                    'id' => $this->subtype->id,
                    'name' => $this->subtype->name,
                    'type' => $this->subtype->type,
                ],
            ]),
            'jumppoints' => new JumppointResource($this->jumppoint(), true),
            'time_modified' => $this->time_modified,
        ];
    }
}
