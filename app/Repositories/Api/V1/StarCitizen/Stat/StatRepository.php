<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 19.01.2017
 * Time: 13:35
 */

namespace App\Repositories\Api\V1\StarCitizen\Stat;

use App\Models\Api\StarCitizen\Stat;
use App\Repositories\AbstractBaseRepository as BaseRepository;
use App\Repositories\Api\V1\StarCitizen\Interfaces\Stat\StatRepositoryInterface;
use App\Transformers\Api\V1\StarCitizen\Stat\ShipTransformer;

/**
 * Class StatsRepository
 */
class StatRepository extends BaseRepository implements StatRepositoryInterface
{
    /**
     * @var \App\Transformers\Api\V1\StarCitizen\Stat\ShipTransformer
     */
    private $transformer;

    /**
     * StatsRepository constructor.
     */
    public function __construct()
    {
        $this->transformer = new ShipTransformer();
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
