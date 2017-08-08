<?php
/**
 * User: Keonie
 * Date: 02.08.2017 17:25
 */

namespace App\Repositories\StarCitizen\APIv1;

use App\Models\Jumppoint;
use App\Repositories\StarCitizen\BaseStarCitizenRepository;
use App\Repositories\StarCitizen\Interfaces\JumppointTunnelInterface;
use App\Transformers\StarCitizen\Starmap\JumppointTunnelListTransformer;
use App\Transformers\StarCitizen\Starmap\JumppointTunnelTransformer;
use InvalidArgumentException;
use App\Models\Starsystem;

/**
 * Class JumppointRepository
 * @package App\Repositories\StarCitizen\APIv1
 */
class JumppointTunnelRepository extends BaseStarCitizenRepository implements JumppointTunnelInterface
{

    /**
     * Get a List of all Jumpoint Tunnels
     * @return $this List of JumpointTunnels
     */
    public function getJumppointTunnelList()
    {
        app('Log')::info(make_name_readable(__FUNCTION__));

        $this->dataToTransform = Jumppoint::all()->toArray();
        return $this->collection()->withTransformer(JumppointTunnelTransformer::class);
    }

    /**
     * Get a Jumpoint Tunnel
     * @param $cig_id
     *
     * @return $this one Jummpointtunnel
     */
    public function getJumppointTunnel($cig_id)
    {
        app('Log')::info(make_name_readable(__FUNCTION__), ['cig_id' => $cig_id]);

        $jumppointTunnelQueryData = Jumppoint::where('cig_id', $cig_id);
        if (is_null($jumppointTunnelQueryData)) {
            throw new InvalidArgumentException("No Jumppoint for id {$cig_id} found!");
        }

        return $this->withTransformer(JumppointTunnelTransformer::class)->transform($jumppointTunnelQueryData);

    }

    /**
     * Get a List of Jumpointtunnels for the System
     * @param $systemName
     *
     * @return $this List of JumpointTunnels
     */
    public function getJumppointTunnelForSystem($systemName)
    {
        app('Log')::info(make_name_readable(__FUNCTION__), ['SystemName' => $systemName]);

        $systemQueryData = Starsystem::where('code', $systemName)
            ->orderBy(self::TIME_GROUP_FIELD, 'DESC')
            ->firstOrFail();

        if (is_null($systemQueryData)) {
            throw new InvalidArgumentException("System " . $systemName . " not found!");
        }

        $jumppointtunnelQueryData = Jumppoint::where('entry_cig_system_id', $systemQueryData->cig_id)
            ->orWhere('exit_cig_system_id', $systemQueryData->cig_id);

        return $this->withTransformer(JumppointTunnelListTransformer::class)->transform($jumppointtunnelQueryData);
    }
}