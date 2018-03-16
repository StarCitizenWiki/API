<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 19.01.2017
 * Time: 13:35
 */

namespace App\Repositories\StarCitizen\ApiV1;

use App\Models\StarCitizen\Stats;
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
        $stats = Stats::orderByDesc('created_at')->first();

        return $this->manager->item($stats, StatsTransformer::class);
    }

    public function getFans()
    {
        $stats = Stats::select('fans')->orderByDesc('created_at')->first();

        return $this->manager->item($stats, FansTransformer::class);
    }

    public function getFleet()
    {
        $stats = Stats::select('fleet')->orderByDesc('created_at')->first();

        return $this->manager->item($stats, FleetTransformer::class);
    }

    public function getFunds()
    {
        $stats = Stats::select('funds')->orderByDesc('created_at')->first();

        return $this->manager->item($stats, FundsTransformer::class);
    }
}
