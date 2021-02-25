<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\StarCitizen\Stat;

use App\Models\StarCitizen\Stat\Stat;
use App\Transformers\Api\V1\AbstractV1Transformer as V1Transformer;

/**
 * Class StatsTransformer
 */
class StatTransformer extends V1Transformer
{
    /**
     * @param Stat $stat
     *
     * @return array
     */
    public function transform(Stat $stat): array
    {
        return [
            'funds' => $stat->funds,
            'fans' => $stat->fans,
            'fleet' => $stat->fleet,
            'timestamp' => optional($stat)->created_at,
        ];
    }
}
