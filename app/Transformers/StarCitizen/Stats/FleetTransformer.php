<?php declare(strict_types = 1);
/**
 * Created by IntelliJ IDEA.
 * User: Sebastian
 * Date: 04.02.2017
 * Time: 21:29
 */

namespace App\Transformers\StarCitizen\Stats;

use App\Transformers\AbstractBaseTransformer;

/**
 * Class FleetTransformer
 *
 * @package App\Transformers\StarCitizen\Stats
 */
class FleetTransformer extends AbstractBaseTransformer
{
    /**
     * Transformes Stats to only return the fleet
     *
     * @param mixed $stats Data
     *
     * @return array
     */
    public function transform($stats)
    {
        return ['fleet' => $stats['data']['fleet']];
    }
}
