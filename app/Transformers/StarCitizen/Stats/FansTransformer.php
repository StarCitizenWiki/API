<?php declare(strict_types = 1);
/**
 * Created by IntelliJ IDEA.
 * User: Sebastian
 * Date: 04.02.2017
 * Time: 21:29
 */

namespace App\Transformers\StarCitizen\Stats;

use App\Models\Api\StarCitizen\Stat;
use App\Transformers\AbstractBaseTransformer;

/**
 * Class FansTransformer
 */
class FansTransformer extends AbstractBaseTransformer
{
    /**
     * Transforms Stats to only return the fans
     *
     * @param mixed $stats Data
     *
     * @return array
     */
    public function transform(Stat $stats)
    {
        return [
            'fans' => (string) $stats->fans,
        ];
    }
}
