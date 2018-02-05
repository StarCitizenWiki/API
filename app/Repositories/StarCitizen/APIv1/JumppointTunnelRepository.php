<?php
/**
 * User: Keonie
 * Date: 02.08.2017 17:25
 */

namespace App\Repositories\StarCitizen\APIv1;

use App\Models\Starmap\Jumppoint;
use App\Repositories\StarCitizen\BaseStarCitizenRepository;
use App\Repositories\StarCitizen\Interfaces\JumppointTunnelInterface;
use App\Transformers\StarCitizen\Starmap\JumppointTunnelTransformer;
use InvalidArgumentException;
use App\Models\Starsystem;

/**
 * Class JumppointRepository
 * @package App\Repositories\StarCitizen\APIv1
 */
class JumppointTunnelRepository extends BaseStarCitizenRepository implements JumppointTunnelInterface
{

    const TIME_GROUP_FIELD = 'created_at';

    /**
     * Get a List of all Jumpoint Tunnels
     * @return $this List of JumpointTunnels
     */
    public function getJumppointTunnelList()
    {
        app('Log')::info(make_name_readable(__FUNCTION__));

        $this->dataToTransform = Jumppoint::groupBy('cig_id')
            ->havingRaw(self::TIME_GROUP_FIELD.' = max('.self::TIME_GROUP_FIELD.')')
            ->get()
            ->toArray();
        return $this->collection()->withTransformer(JumppointTunnelTransformer::class);
    }

    /**
     * Get a Jumpoint Tunnel
     * @param $cig_id
     *
     * @return $this one Jummpointtunnel
     */
    public function getJumppointTunnelById($cig_id)
    {
        app('Log')::info(make_name_readable(__FUNCTION__), ['cig_id' => $cig_id]);

        $jumppointTunnelQueryData = Jumppoint::where('cig_id', $cig_id)
            ->groupBy('cig_id')
            ->havingRaw(self::TIME_GROUP_FIELD.' = max('.self::TIME_GROUP_FIELD.')')
            ->get()
            ->toArray();

        if (is_null($jumppointTunnelQueryData)) {
            throw new InvalidArgumentException("No JumppointTunnel for id {$cig_id} found!");
        }

        //TODO Transformer checken -> gibt  "transformer": {} zurueck
        return $this->withTransformer(JumppointTunnelTransformer::class)->transform($jumppointTunnelQueryData);
    }

    /**
     * Get a List of Jumpointtunnels for the System
     * @param $systemName string Name (Code) of System
     *
     * @return $this List of JumpointTunnels
     */
    public function getJumppointTunnelBySystem($systemName)
    {
        app('Log')::info(make_name_readable(__FUNCTION__), ['SystemName' => $systemName]);

        $systemQueryData = Starsystem::where('code', $systemName)
            ->orderBy(self::TIME_GROUP_FIELD, 'DESC')
            ->firstOrFail();

        if (is_null($systemQueryData)) {
            throw new InvalidArgumentException("System {$systemName} not found!");
        }

        $jumppointTunnelQueryData = Jumppoint::where('entry_cig_system_id', $systemQueryData->cig_id)
            ->orWhere('exit_cig_system_id', $systemQueryData->cig_id)
            ->groupBy('cig_id')
            ->havingRaw(self::TIME_GROUP_FIELD.' = max('.self::TIME_GROUP_FIELD.')')
            ->get()
            ->toArray();

        return $this->withTransformer(JumppointTunnelTransformer::class)->transform($jumppointTunnelQueryData);
    }

    /**
     * Get a List of Jumppointtunnels for size
     * @param $size
     *
     * @return $this
     */
    public function getJumppointTunnelForBySize($size)
    {
        app('Log')::info(make_name_readable(__FUNCTION__), ['size' => $size]);

        $jumppointTunnelQueryData = Jumppoint::where('size', $size)
            ->groupBy('cig_id')
            ->havingRaw(self::TIME_GROUP_FIELD.' = max('.self::TIME_GROUP_FIELD.')')
            ->get()
            ->toArray();

        if (is_null($jumppointTunnelQueryData)) {
            throw new InvalidArgumentException("No Jumppoint for size {$size} found!");
        }
        return $this->withTransformer(JumppointTunnelTransformer::class)->transform($jumppointTunnelQueryData);
    }
}