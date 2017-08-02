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
 * Class StatsTransformer
 *
 * @package App\Transformers\StarCitizen\Stats
 */
class StatsTransformer extends AbstractBaseTransformer
{
    protected $validFields = [
        'fans',
        'funds',
        'fleet',
        'chart',
    ];

    /**
     * Returns all stats
     *
     * @param mixed $stats Data
     *
     * @return mixed
     */
    public function transform($stats)
    {
        $stats = $stats['data'];
        $data = [
            'fans'  => (string) $stats['fans'],
            'fleet' => (string) $stats['fleet'],
            'funds' => (string) $stats['funds'],
            'chart' => $stats['chart'],
        ];

        return $this->filterData($data);
    }
}
