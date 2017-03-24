<?php
/**
 * Created by IntelliJ IDEA.
 * User: Sebastian
 * Date: 04.02.2017
 * Time: 21:29
 */

namespace App\Transformers\StarCitizen\Stats;

use App\Transformers\BaseAPITransformerInterface;
use League\Fractal\TransformerAbstract;

/**
 * Class StatsTransformer
 *
 * @package App\Transformers\StarCitizen\Stats
 */
class StatsTransformer extends TransformerAbstract implements BaseAPITransformerInterface
{
    /**
     * Returns all stats
     *
     * @param mixed $stats Data
     *
     * @return mixed
     */
    public function transform($stats)
    {
        return $stats;
    }
}
