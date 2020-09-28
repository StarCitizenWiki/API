<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\StarCitizen\Starmap;

use App\Models\Api\StarCitizen\Starmap\Starsystem\Starsystem;
use App\Transformers\Api\V1\StarCitizen\AbstractTranslationTransformer;

/**
 * Class StarsystemTransformer
 */
class StarsystemTransformer extends AbstractTranslationTransformer
{
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
            'name' => $starsystem->name,
            'status' => $starsystem->status,
            'time_modified' => $starsystem->time_modified,
            'type' => $starsystem->type,
            'position' => [
                'x' => $starsystem->position_x,
                'y' => $starsystem->position_y,
                'z' => $starsystem->position_z,
            ],
            'info_url' => $starsystem->info_url,
            'description' => $this->getTranslation($starsystem),
/*            'affiliation' => [
                'name' => $starsystem->affiliation->name,
                'code' => $starsystem->affiliation->code,
                'color' => $starsystem->affiliation->color,
            ],*/
            'aggregated' => [
                'size' => $starsystem->aggregated_size,
                'population' => $starsystem->aggregated_population,
                'economy' => $starsystem->aggregated_economy,
                'danger' => $starsystem->aggregated_danger,
            ],
        ];
    }
}
