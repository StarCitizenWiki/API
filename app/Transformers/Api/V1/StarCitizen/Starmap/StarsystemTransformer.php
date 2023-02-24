<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\StarCitizen\Starmap;

use App\Models\StarCitizen\Starmap\Starsystem\Starsystem;
use App\Transformers\Api\V1\StarCitizen\AbstractTranslationTransformer;
use League\Fractal\Resource\Collection;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'starsystem',
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
        new OA\Property(property: 'time_modified', type: 'timestamp'),
        new OA\Property(
            property: 'celestial_object',
            properties: [
                new OA\Property(
                    property: 'celestial_object',
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/celestial_object',
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
                            ref: '#/components/schemas/jumppoint',
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
class StarsystemTransformer extends AbstractTranslationTransformer
{
    protected array $availableIncludes = [
        'jumppoints',
        'celestial_objects',
    ];

    protected array $defaultIncludes = [
        'affiliation',
    ];

    /**
     * @param Starsystem $starsystem
     *
     * @return array
     */
    public function transform(Starsystem $starsystem): array
    {
        return [
            'id' => $starsystem->cig_id,
            'code' => $starsystem->code,
            'system_api_url' => $this->makeApiUrl(self::STARMAP_STARSYSTEM_SHOW, $starsystem->code),
            'name' => $starsystem->name,
            'status' => $starsystem->status,
            'type' => $starsystem->type,

            'position' => [
                'x' => $starsystem->position_x,
                'y' => $starsystem->position_y,
                'z' => $starsystem->position_z,
            ],

            'frost_line' => $starsystem->frost_line,
            'habitable_zone_inner' => $starsystem->habitable_zone_inner,
            'habitable_zone_outer' => $starsystem->habitable_zone_outer,

            'info_url' => $starsystem->info_url,

            'description' => $this->getTranslation($starsystem),

            'aggregated' => [
                'size' => $starsystem->aggregated_size,
                'population' => $starsystem->aggregated_population,
                'economy' => $starsystem->aggregated_economy,
                'danger' => $starsystem->aggregated_danger,

                'stars' => $starsystem->stars_count,
                'planets' => $starsystem->planets_count,
                'moons' => $starsystem->moons_count,
                'stations' => $starsystem->stations_count,
            ],

            'updated_at' => $starsystem->time_modified,
        ];
    }

    /**
     * Starsystem affiliation, included by default
     *
     * @param Starsystem $starsystem
     *
     * @return Collection
     */
    public function includeAffiliation(Starsystem $starsystem): Collection
    {
        return $this->collection($starsystem->affiliation, new AffiliationTransformer(), 'affiliation');
    }

    /**
     * System celestial objcets like planets, jumppoints, asteroid belts, ...
     *
     * @param Starsystem $starsystem
     *
     * @return Collection
     */
    public function includeCelestialObjects(Starsystem $starsystem): Collection
    {
        return $this->collection(
            $starsystem->celestialObjects,
            $this->makeTransformer(CelestialObjectTransformer::class, $this),
            'celestial_object'
        );
    }

    /**
     * Jump points starting in this system
     *
     * @param Starsystem $starsystem
     *
     * @return Collection
     */
    public function includeJumppoints(Starsystem $starsystem): Collection
    {
        return $this->collection($starsystem->jumppoints(), new JumppointTransformer(), 'jumppoint');
    }
}
