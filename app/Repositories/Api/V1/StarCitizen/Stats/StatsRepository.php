<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 19.01.2017
 * Time: 13:35
 */

namespace App\Repositories\Api\V1\StarCitizen\Stats;

use App\Models\Api\StarCitizen\Stat;
use App\Repositories\Api\V1\StarCitizen\Interfaces\Stats\StatsRepositoryInterface;
use App\Transformers\Api\V1\StarCitizen\Stats\StatsTransformer;
use Dingo\Api\Routing\Helpers;

/**
 * Class StatsRepository
 */
class StatsRepository implements StatsRepositoryInterface
{
    use Helpers;

    private $transformer;

    /**
     * StatsRepository constructor.
     */
    public function __construct()
    {
        $this->transformer = new StatsTransformer();
    }

    /**
     * Returns all Crowdfund Stats
     * https://robertsspaceindustries.com/api/stats/getCrowdfundStats
     *
     * @return \Dingo\Api\Http\Response
     */
    public function getAll()
    {
        $stats = Stat::orderByDesc('created_at')->paginate();

        if (null === $stats) {
            return $this->emptyStat();
        }

        return $this->response->paginator($stats, $this->transformer);
    }

    /**
     * @return \Dingo\Api\Http\Response
     */
    public function getLatest()
    {
        $stat = Stat::orderByDesc('created_at')->first();

        if (null === $stat) {
            return $this->emptyStat();
        }

        return $this->response->item($stat, $this->transformer);
    }

    /**
     * Empty Model
     *
     * @return \Dingo\Api\Http\Response
     */
    private function emptyStat()
    {
        return $this->response->item(new Stat(), $this->transformer);
    }
}
