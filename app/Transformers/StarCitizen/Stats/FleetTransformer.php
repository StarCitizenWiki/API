<?php declare(strict_types = 1);
/**
 * Created by IntelliJ IDEA.
 * User: Sebastian
 * Date: 04.02.2017
 * Time: 21:29
 */

namespace App\Transformers\StarCitizen\Stats;

use App\Models\StarCitizen\Stats;
use App\Transformers\AbstractBaseTransformer;

/**
 * Class FleetTransformer
 */
class FleetTransformer extends AbstractBaseTransformer
{
    /**
     * Transforms Stats to only return the fleet
     *
     * @param \App\Models\StarCitizen\Stats $stats Data
     *
     * @return array
     */
    public function transform(Stats $stats)
    {
        return [
            'fleet' => (string) $stats->fleet,
        ];
    }
}
