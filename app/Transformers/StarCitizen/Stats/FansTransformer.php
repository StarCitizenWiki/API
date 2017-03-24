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
 * Class FansTransformer
 *
 * @package App\Transformers\StarCitizen\Stats
 */
class FansTransformer extends TransformerAbstract implements BaseAPITransformerInterface
{
    /**
     * Transformes Stats to only return the fans
     *
     * @param mixed $stats Data
     *
     * @return array
     */
    public function transform($stats)
    {
        return ['fans' => $stats['data']['fans']];
    }
}
