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
                'id' => $jumppoint->entry_cig_id,
                'system_id' => $jumppoint->entry_cig_system_id,
                'status' => $jumppoint->entry_status,
                'code' => $jumppoint->entry_code,
                'designation' => $jumppoint->entry_designation,
            ],
            'exit' => [
                'id' => $jumppoint->exit_cig_id,
                'system_id' => $jumppoint->exit_cig_system_id,
                'status' => $jumppoint->exit_status,
                'code' => $jumppoint->exit_code,
                'designation' => $jumppoint->exit_designation,
            ],
        ];
    }
}
