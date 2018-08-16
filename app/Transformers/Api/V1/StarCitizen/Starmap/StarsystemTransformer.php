<?php
/**
 * User: Keonie
 * Date: 07.08.2018 14:17
 */

namespace App\Transformers\Api\V1\StarCitizen\Starmap;

use App\Transformers\Api\V1\StarCitizen\AbstractTranslationTransformer;
use App\Models\Api\StarCitizen\Starmap\Starsystem\Starsystem;

/**
 * Class StarsystemTransformer
 * @package App\Transformers\Api\V1\StarCitizen\Starmap
 */
class StarsystemTransformer extends AbstractTranslationTransformer
{

    /**
     * @param \App\Models\Api\StarCitizen\Starmap\Starsystem\Starsystem $starsystem
     *
     * @return array
     */
    public function transform(Starsystem $starsystem)
    {
        return [
            'id' => $starsystem->cig_id,
            'code' => $starsystem->code,
            'name' => $starsystem->name,
            'status' => $starsystem->code,
            'time_modified' => $starsystem->cig_time_modified,
            'type' => $starsystem->type,
            'position' => [
              'x' => $starsystem->position_x,
              'y' => $starsystem->position_y,
              'z' => $starsystem->position_z,
            ],
            'info_url' => $starsystem->info_url,
            'description' => $this->getTranslation($starsystem->description),
            'affiliation' => [
                'name' => $starsystem->affiliation->name,
                'code' => $starsystem->affiliation->code,
                'color' => $starsystem->subtype->code,
            ],
            'aggregated' => [
                'size' => $starsystem->aggregated_size,
                'population' => $starsystem->aggregated_population,
                'economy' => $starsystem->aggregated_economy,
                'danger' => $starsystem->aggregated_danger,
            ],
        ];
    }

}

