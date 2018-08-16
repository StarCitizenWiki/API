<?php
/**
 * User: Keonie
 * Date: 07.08.2018 15:08
 */

namespace App\Transformers\Api\V1\StarCitizen\Starmap;

use App\Transformers\Api\V1\StarCitizen\AbstractTranslationTransformer;
use App\Models\Api\StarCitizen\Starmap\Jumppoint\Jumppoint;

/**
 * Class JumppointTransformer
 * @package App\Transformers\Api\V1\StarCitizen\Starmap
 */
class JumppointTransformer extends AbstractTranslationTransformer
{

    /**
     * @param \App\Models\Api\StarCitizen\Starmap\Jumppoint\Jumppoint $jumppoint
     *
     * @return array
     */
    public function transform(Jumppoint $jumppoint)
    {
        return [
            'id' => $jumppoint->cig_id,
            'size' => $jumppoint->size,
            'direction'  => $jumppoint->direction,
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
            ]
        ];

    }
}