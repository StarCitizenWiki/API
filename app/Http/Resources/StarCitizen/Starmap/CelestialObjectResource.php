<?php

declare(strict_types=1);

namespace App\Http\Resources\StarCitizen\Starmap;

use App\Http\Resources\AbstractBaseResource;
use App\Http\Resources\TranslationResolver;
use App\Models\StarCitizen\Starmap\Jumppoint;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'celestial_object',
    title: 'Celestial Object',
    properties: [
        new OA\Property(property: 'id', type: 'integer'),
        new OA\Property(property: 'code', type: 'string'),
        new OA\Property(property: 'system_id', type: 'integer'),
        new OA\Property(property: 'celestial_object_api_url', type: 'string'),
        new OA\Property(property: 'web_url', type: 'string'),
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
        new OA\Property(
            property: 'starsystem',
            properties: [
                new OA\Property(property: 'id', type: 'integer', nullable: true),
                new OA\Property(property: 'code', type: 'string', nullable: true),
                new OA\Property(property: 'name', type: 'string', nullable: true),
            ],
            type: 'object',
            nullable: true
        ),
        new OA\Property(property: 'time_modified', type: 'string'),
    ],
    type: 'object'
)]
class CelestialObjectResource extends AbstractBaseResource
{
    public function toArray($request): array
    {
        $jumppoint = $this->relationLoaded('jumppointEntry') || $this->relationLoaded('jumppointExit')
            ? $this->jumppointEntry ?? $this->jumppointExit
            : $this->jumppoint();

        return [
            'id' => $this->cig_id,
            'code' => $this->code,
            'system_id' => $this->starsystem_id,
            'link' => route(
                'celestial-objects.show',
                ['code' => $this->code]
            ),
            'web_url' => route('web.starmap.celestial-objects.show', ['code' => $this->code]),
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

            'description' => TranslationResolver::resolve($this, $request),

            'sensor' => [
                'population' => $this->sensor_population,
                'economy' => $this->sensor_economy,
                'danger' => $this->sensor_danger,
            ],

            'size' => $this->size,

            'parent_id' => $this->parent_id,

            'affiliation' => AffiliationResource::collection($this->whenLoaded('affiliation')),
            'starsystem' => $this->whenLoaded('starsystem', function (): array {
                return [
                    'id' => $this->starsystem?->cig_id,
                    'code' => $this->starsystem?->code,
                    'name' => $this->starsystem?->name,
                ];
            }),
            $this->mergeWhen($this->whenLoaded('subtype'), [
                'sub_type' => [
                    'id' => $this->subtype->id,
                    'name' => $this->subtype->name,
                    'type' => $this->subtype->type,
                ],
            ]),
            'jumppoints' => $jumppoint instanceof Jumppoint ? new JumppointResource($jumppoint, true) : null,
            'time_modified' => $this->time_modified,
        ];
    }
}
