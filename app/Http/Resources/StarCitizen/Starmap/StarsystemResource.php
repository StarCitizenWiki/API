<?php

declare(strict_types=1);

namespace App\Http\Resources\StarCitizen\Starmap;

use App\Http\Resources\AbstractTranslationResource;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'starsystem_v2',
    title: 'Starsystem',
    properties: [
        new OA\Property(property: 'id', type: 'integer'),
        new OA\Property(property: 'code', type: 'string'),
        new OA\Property(property: 'system_api_url', type: 'string'),
        new OA\Property(property: 'name', type: 'string'),
        new OA\Property(property: 'status', type: 'string'),
        new OA\Property(property: 'type', type: 'string'),
        new OA\Property(
            property: 'position',
            properties: [
                new OA\Property(property: 'x', type: 'float'),
                new OA\Property(property: 'y', type: 'float'),
                new OA\Property(property: 'z', type: 'float'),
            ],
            type: 'object'
        ),
        new OA\Property(property: 'frost_line', type: 'float'),
        new OA\Property(property: 'habitable_zone_inner', type: 'float'),
        new OA\Property(property: 'habitable_zone_outer', type: 'float'),
        new OA\Property(property: 'info_url', type: 'string'),
        new OA\Property(property: 'description', type: 'object'),

        new OA\Property(
            property: 'aggregated',
            properties: [
                new OA\Property(property: 'size', type: 'float'),
                new OA\Property(property: 'population', type: 'float'),
                new OA\Property(property: 'economy', type: 'float'),
                new OA\Property(property: 'danger', type: 'float'),

                new OA\Property(property: 'stars', type: 'integer'),
                new OA\Property(property: 'planets', type: 'integer'),
                new OA\Property(property: 'moons', type: 'integer'),
                new OA\Property(property: 'stations', type: 'integer'),
            ],
            type: 'object'
        ),
        new OA\Property(property: 'time_modified', type: 'string'),
        new OA\Property(
            property: 'celestial_object',
            properties: [
                new OA\Property(
                    property: 'celestial_object',
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/celestial_object_v2',
                            type: 'array',
                            items: new OA\Items(),
                        ),
                    ],
                    type: 'object',
                    nullable: true
                ),
            ],
            nullable: true,
        ),
        new OA\Property(
            property: 'jumppoints',
            properties: [
                new OA\Property(
                    property: 'jumppoints',
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/jumppoint_v2',
                            type: 'array',
                            items: new OA\Items(),
                        ),
                    ],
                    type: 'object',
                    nullable: true
                ),
            ],
            nullable: true,
        ),
    ],
    type: 'object'
)]
class StarsystemResource extends AbstractTranslationResource
{
    public static function validIncludes(): array
    {
        return [
            'affiliation',
            'celestialObjects',
        ];
    }

    /**
     * @param Request $request
     *
     * @return array
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->cig_id,
            'code' => $this->code,
            'system_api_url' => $this->makeApiUrl(self::STARMAP_STARSYSTEM_SHOW, $this->code),
            'name' => $this->name,
            'status' => $this->status,
            'type' => $this->type,

            'position' => [
                'x' => $this->position_x,
                'y' => $this->position_y,
                'z' => $this->position_z,
            ],

            'frost_line' => $this->frost_line,
            'habitable_zone_inner' => $this->habitable_zone_inner,
            'habitable_zone_outer' => $this->habitable_zone_outer,

            'info_url' => $this->info_url,

            'description' => $this->getTranslation($this, $request),

            'aggregated' => [
                'size' => $this->aggregated_size,
                'population' => $this->aggregated_population,
                'economy' => $this->aggregated_economy,
                'danger' => $this->aggregated_danger,

                'stars' => $this->stars_count,
                'planets' => $this->planets_count,
                'moons' => $this->moons_count,
                'stations' => $this->stations_count,
            ],

            'affiliation' => AffiliationResource::collection($this->whenLoaded('affiliation')),
            'celestial_objects' => CelestialObjectResource::collection($this->whenLoaded('celestialObjects')),
            'jumppoints' => JumppointResource::collection($this->jumppoints()),

            'updated_at' => $this->time_modified,
        ];
    }
}
