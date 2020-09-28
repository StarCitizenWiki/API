<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\StarCitizen\Starmap;

use App\Models\Api\StarCitizen\Starmap\Jumppoint\Jumppoint;
use App\Transformers\Api\V1\StarCitizen\AbstractTranslationTransformer;

/**
 * Class JumppointTransformer
 */
class JumppointTransformer extends AbstractTranslationTransformer
{
    /**
     * @param Jumppoint $jumppoint
     *
     * @return array
     */
    public function transform(Jumppoint $jumppoint): array
    {
        return [
            'id' => $jumppoint->cig_id,
            'size' => $jumppoint->size,
            'direction' => $jumppoint->direction,
            'entry' => [
                'id' => $jumppoint->entry->cig_id,
                'system_id' => $jumppoint->entry->starsystem_id,
                'system_api_url' => $this->makeApiUrl(
                    self::STARMAP_STARSYSTEM_SHOW,
                    $jumppoint->entry->starsystem_id
                ),
                'celestial_object_api_url' => $this->makeApiUrl(
                    self::STARMAP_CELESTIAL_OBJECTS_SHOW,
                    $jumppoint->entry->code
                ),
                'status' => $jumppoint->entry_status,
                'code' => $jumppoint->entry->code,
                'designation' => $jumppoint->entry->designation,
            ],
            'exit' => [
                'id' => $jumppoint->exit->cig_id,
                'system_id' => $jumppoint->exit->starsystem_id,
                'system_api_url' => $this->makeApiUrl(
                    self::STARMAP_STARSYSTEM_SHOW,
                    $jumppoint->exit->starsystem_id
                ),
                'celestial_object_api_url' => $this->makeApiUrl(
                    self::STARMAP_CELESTIAL_OBJECTS_SHOW,
                    $jumppoint->exit->code
                ),
                'status' => $jumppoint->exit_status,
                'code' => $jumppoint->exit->code,
                'designation' => $jumppoint->exit->designation,
            ],
        ];
    }
}
