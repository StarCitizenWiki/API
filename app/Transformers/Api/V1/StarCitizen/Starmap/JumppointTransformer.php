<?php declare(strict_types=1);
/**
 * User: Keonie
 * Date: 07.08.2018 15:08
 */

namespace App\Transformers\Api\V1\StarCitizen\Starmap;

use App\Models\Api\StarCitizen\Starmap\Jumppoint\Jumppoint;
use App\Transformers\Api\V1\StarCitizen\AbstractTranslationTransformer;

/**
 * Class JumppointTransformer
 */
class JumppointTransformer extends AbstractTranslationTransformer
{

    /**
     * @param \App\Models\Api\StarCitizen\Starmap\Jumppoint\Jumppoint $jumppoint
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
