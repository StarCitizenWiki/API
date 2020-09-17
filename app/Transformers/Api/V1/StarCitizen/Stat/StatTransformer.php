<?php declare(strict_types = 1);

namespace App\Transformers\Api\V1\StarCitizen\Stat;

use App\Models\Api\StarCitizen\Stat\Stat;
use League\Fractal\TransformerAbstract;

/**
 * Class StatsTransformer
 */
class StatTransformer extends TransformerAbstract
{
    /**
     * @param Stat $stat
     *
     * @return array
     */
    public function transform(Stat $stat)
    {
        return [
            'funds' => $stat->funds,
            'fans' => $stat->fans,
            'fleet' => $stat->fleet,
            'timestamp' => optional($stat->created_at),
        ];
    }
}
