<?php
/**
 * User: Keonie
 * Date: 02.08.2017 17:25
 */

namespace App\Repositories\StarCitizen\APIv1;

use App\Models\CelestialObject;
use App\Models\Jumppoint;
use App\Models\Starsystem;
use App\Repositories\StarCitizen\BaseStarCitizenRepository;
use App\Repositories\StarCitizen\Interfaces\StarmapInterface;
use App\Transformers\StarCitizen\Starmap\AsteroidbeltsTransformer;
use App\Transformers\StarCitizen\Starmap\CelestialObjectTransformer;
use App\Transformers\StarCitizen\Starmap\JumppointsTransformer;
use App\Transformers\StarCitizen\Starmap\LandingzonesTransformer;
use App\Transformers\StarCitizen\Starmap\MoonsTransformer;
use App\Transformers\StarCitizen\Starmap\PlanetsTransformer;
use App\Transformers\StarCitizen\Starmap\SpacestationsTransformer;
use App\Transformers\StarCitizen\Starmap\StarsTransformer;
use App\Transformers\StarCitizen\Starmap\SystemListTransformer;
use App\Transformers\StarCitizen\Starmap\SystemTransformer;
use InvalidArgumentException;

/**
 * Class JumppointRepository
 * @package App\Repositories\StarCitizen\APIv1
 */
class JumppointRepository extends BaseStarCitizenRepository implements JumppointTunnelInterface
{

    public function getJumppointList()
    {
        app('Log')::info(make_name_readable(__FUNCTION__));

        $this->dataToTransform = Jumppoint::all()->toArray();
        return $this->collection()->withTransformer(SystemListTransformer::class);
    }

    public function getJumppointTunnel($cig_id)
    {
        app('Log')::info(make_name_readable(__FUNCTION__), ['cig_id' => $cig_id]);

        $jumppointQueryData = Jumppoint::where('cig_id', $cig_id);

        return $this->withTransformer(StarsTransformer::class)->transform($jumppointQueryData);

    }
}