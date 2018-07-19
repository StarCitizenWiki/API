<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 19.01.2017
 * Time: 13:35
 */

namespace App\Repositories\StarCitizen\ApiV1;

use App\Models\Api\StarCitizen\Stat;
use App\Repositories\AbstractBaseRepository as BaseRepository;
use App\Repositories\StarCitizen\Interfaces\Stats\StatsRepositoryInterface;
use App\Transformers\StarCitizen\Stats\FansTransformer;
use App\Transformers\StarCitizen\Stats\FleetTransformer;
use App\Transformers\StarCitizen\Stats\FundsTransformer;
use App\Transformers\StarCitizen\Stats\StatsTransformer;
use Spatie\Fractal\Fractal;

/**
 * Class StatsRepository
 */
class StatsRepository extends BaseRepository implements StatsRepositoryInterface
{

    /**
     * Returns all Crowdfund Stats
     * https://robertsspaceindustries.com/api/stats/getCrowdfundStats
     *
     * @return Fractal
     */
    public function getAll(): Fractal
    {
        $stats = Stat::orderByDesc('created_at')->first();

        return $this->manager->item($stats, StatsTransformer::class);
    }

    /**
     * @return \Spatie\Fractal\Fractal
     */
    public function getFans(): Fractal
    {
        $stats = Stat::select('fans')->orderByDesc('created_at')->first();

        return $this->manager->item($stats, FansTransformer::class);
    }

    /**
     * @return \Spatie\Fractal\Fractal
     */
    public function getFleet(): Fractal
    {
        $stats = Stat::select('fleet')->orderByDesc('created_at')->first();

        return $this->manager->item($stats, FleetTransformer::class);
    }

    /**
     * @return \Spatie\Fractal\Fractal
     */
    public function getFunds(): Fractal
    {
        $stats = Stat::select('funds')->orderByDesc('created_at')->first();

        return $this->manager->item($stats, FundsTransformer::class);
    }
}
