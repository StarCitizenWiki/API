<?php declare(strict_types = 1);
/**
 * User: Keonie
 * Date: 02.08.2017 17:25
 */

namespace App\Repositories\StarCitizen\Api\v1;

use App\Models\Api\StarCitizen\Starmap\Jumppoint;
use App\Models\Api\StarCitizen\Starmap\Starsystem;
use App\Repositories\StarCitizen\Interfaces\JumppointTunnelInterface;
use App\Transformers\StarCitizen\Starmap\JumppointTunnelTransformer;
use InvalidArgumentException;

/**
 * Class JumppointRepository
 */
class JumppointTunnelRepository extends AbstractStarCitizenRepository implements JumppointTunnelInterface
{

    const TIME_GROUP_FIELD = 'created_at';

    /**
     * Get a List of all Jumpoint Tunnels
     *
     * @return $this List of JumpointTunnels
     *
     * @throws \App\Exceptions\WrongMethodNameException
     */
    public function getJumppointTunnelList()
    {
        app('Log')::debug(make_name_readable(__FUNCTION__));

        $this->dataToTransform = Jumppoint::groupBy('cig_id')
            ->havingRaw(self::TIME_GROUP_FIELD.' = max('.self::TIME_GROUP_FIELD.')')
            ->get()
            ->toArray();

        return $this->collection()->withTransformer(JumppointTunnelTransformer::class);
    }

    /**
     * Get a Jumpoint Tunnel
     *
     * @param $cig_id
     *
     * @return $this one Jummpointtunnel
     *
     * @throws \App\Exceptions\WrongMethodNameException
     */
    public function getJumppointTunnelById($cig_id)
    {
        app('Log')::debug(make_name_readable(__FUNCTION__), ['cig_id' => $cig_id]);

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
     *
     * @param string $systemName Name (Code) of System
     *
     * @return $this List of JumpointTunnels
     *
     * @throws \App\Exceptions\WrongMethodNameException
     */
    public function getJumppointTunnelBySystem($systemName)
    {
        app('Log')::debug(make_name_readable(__FUNCTION__), ['SystemName' => $systemName]);

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
     *
     * @param $size
     *
     * @return $this
     *
     * @throws \App\Exceptions\WrongMethodNameException
     */
    public function getJumppointTunnelForBySize($size)
    {
        app('Log')::debug(make_name_readable(__FUNCTION__), ['size' => $size]);

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