<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 19.01.2017
 * Time: 13:35
 */

namespace App\Repositories\Api\V1\StarCitizen\Stats;

use App\Models\Api\StarCitizen\Stat;
use App\Http\Resources\Api\V1\StarCitizen\Stat\Stat as StatResource;
use App\Repositories\AbstractBaseRepository as BaseRepository;
use App\Repositories\Api\V1\StarCitizen\Interfaces\Stats\StatsRepositoryInterface;

/**
 * Class StatsRepository
 */
class StatsRepository implements StatsRepositoryInterface
{

    /**
     * Returns all Crowdfund Stats
     * https://robertsspaceindustries.com/api/stats/getCrowdfundStats
     *
     * @return Fractal
     */
    public function getAll()
    {
        $stats = Stat::orderByDesc('created_at')->paginate();

        return new StatResource(Stat::find(1111111));

        return StatResource::collection($stats);
    }

    /**
     * @return \Spatie\Fractal\Fractal
     */
    public function getFans(): StatResource
    {
        $stats = Stat::select('fans')->orderByDesc('created_at')->first();

        return $this->manager->item($stats, FansTransformer::class);
    }

    /**
     * @return \Spatie\Fractal\Fractal
     */
    public function getFleet(): StatResource
    {
        $stats = Stat::select('fleet')->orderByDesc('created_at')->first();

        return $this->manager->item($stats, FleetTransformer::class);
    }

    /**
     * @return \Spatie\Fractal\Fractal
     */
    public function getFunds(): StatResource
    {
        $stats = Stat::select('funds')->orderByDesc('created_at')->first();

        return $this->manager->item($stats, FundsTransformer::class);
    }
}
