<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\StarCitizen\Starmap;

use App\Models\StarCitizen\Starmap\Starsystem\Starsystem;
use App\Transformers\Api\V1\StarCitizen\AbstractTranslationTransformer;
use League\Fractal\Resource\Collection;

/**
 * Class StarsystemTransformer
 */
class StarsystemTransformer extends AbstractTranslationTransformer
{
    protected $availableIncludes = [
        'jumppoints',
        'celestial_objects',
    ];

    protected $defaultIncludes = [
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
