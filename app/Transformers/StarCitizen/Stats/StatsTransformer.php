<?php declare(strict_types = 1);
/**
 * Created by IntelliJ IDEA.
 * User: Sebastian
 * Date: 04.02.2017
 * Time: 21:29
 */

namespace App\Transformers\StarCitizen\Stats;

use App\Models\StarCitizen\Stat;
use App\Transformers\AbstractBaseTransformer;

/**
 * Class StatsTransformer
 */
class StatsTransformer extends AbstractBaseTransformer
{
    protected $validFields = [
        'fans',
        'funds',
        'fleet',
    ];

    /**
     * Returns all stats
     *
     * @param mixed $stats Data
     *
     * @return mixed
     *
     * @throws \App\Exceptions\InvalidDataException
     */
    public function transform(Stat $stats)
    {
        $data = [
            'fans' => (string) $stats->fans,
            'fleet' => (string) $stats->fleet,
            'funds' => (string) $stats->funds,
        ];

        return $this->filterData($data);
    }
}
