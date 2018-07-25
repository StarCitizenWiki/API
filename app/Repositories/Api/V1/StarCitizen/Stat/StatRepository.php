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
use App\Transformers\Api\V1\StarCitizen\Stat\StatTransformer;

/**
 * Class StatsRepository
 */
class StatRepository extends BaseRepository implements StatRepositoryInterface
{
    /**
     * @var \App\Transformers\Api\V1\StarCitizen\Stat\StatTransformer
     */
    private $transformer;

    /**
     * StatsRepository constructor.
     * @param \App\Transformers\Api\V1\StarCitizen\Stat\StatTransformer $transformer
     */
    public function __construct(StatTransformer $transformer)
    {
        $this->transformer = $transformer;
    }

    /**
     * Returns all Crowdfund Stats
     *
     * @return \Dingo\Api\Http\Response
     */
    public function all()
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
    public function latest()
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
