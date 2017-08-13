<?php
/**
 * User: Keonie
 * Date: 03.08.2017 16:42
 */

namespace App\Transformers\StarCitizen\Starmap;

use App\Transformers\AbstractBaseTransformer;

/**
 * Class JumppointTunnelTransformer
 * @package App\Transformers\StarCitizen\Starmap
 */
class JumppointTunnelTransformer extends AbstractBaseTransformer
{
    const FILTER_FIELDS = ['sourcedata', 'id', 'exclude', 'created_at', 'updated_at'];
    const RENAME_KEYS = ['cig_id' => 'id', 'cig_system_id' => 'system_id'];
    const SUBARRAY_NODES = ['entry', 'exit'];

    /**
     * @param $jumppointTunnel
     *
     * @return mixed
     */
    public function transform($jumppointTunnel)
    {
        $jumppointTunnel = $this->moveToSubarray($jumppointTunnel, static::SUBARRAY_NODES);
        return $this->filterAndRenameFields($jumppointTunnel);
    }
}