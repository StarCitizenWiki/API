<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 24.07.2018
 * Time: 13:59
 */

namespace App\Transformers\Api\V1\StarCitizen\Stats;

use App\Models\Api\StarCitizen\Stat;
use League\Fractal\TransformerAbstract;

/**
 * Class StatsTransformer
 */
class StatsTransformer extends TransformerAbstract
{
    /**
     * @param \App\Models\Api\StarCitizen\Stat $stat
     *
     * @return array
     */
    public function transform(Stat $stat)
    {
        return [
            'funds' => $stat->funds,
            'fans' => $stat->fans,
            'fleet' => $stat->fleet,
            'timestamp' => optional($stat->created_at)->toDateTimeString(),
        ];
    }
}
