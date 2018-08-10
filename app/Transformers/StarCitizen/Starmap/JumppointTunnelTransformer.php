<?php declare(strict_types = 1);
/**
 * User: Keonie
 * Date: 03.08.2017 16:42
 */

namespace App\Transformers\StarCitizen\Starmap;


use League\Fractal\TransformerAbstract;

/**
 * Class JumppointTunnelTransformer
 */
class JumppointTunnelTransformer extends TransformerAbstract
{
    const FILTER_FIELDS = ['sourcedata', 'id', 'exclude', 'created_at', 'updated_at'];
    const RENAME_KEYS = ['cig_id' => 'id', 'cig_system_id' => 'system_id'];
    const SUBARRAY_NODES = ['entry', 'exit'];

    /**
     * @param array $jumppointTunnelInput
     *
     * @return mixed
     */
    public function transform($jumppointTunnelInput)
    {
        // One Array has to be in an Array of Arrays
        if (!array_key_exists(0, $jumppointTunnelInput)) {
            $tmpJumppointTunnel = $jumppointTunnelInput;
            $jumppointTunnels = [];
            array_push($jumppointTunnels, $tmpJumppointTunnel);
        } else {
            $jumppointTunnels = $jumppointTunnelInput;
        }

        foreach ($jumppointTunnels as &$jumppointTunnel) {
            $jumppointTunnel = $this->moveToSubarray($jumppointTunnel, static::SUBARRAY_NODES);
            $jumppointTunnel = $this->filterAndRenameFields($jumppointTunnel);
        }

        return $jumppointTunnels;
    }
}
