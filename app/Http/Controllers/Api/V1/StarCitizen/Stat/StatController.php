<?php declare(strict_types = 1);

namespace App\Http\Controllers\Api\V1\StarCitizen\Stat;

use App\Http\Controllers\Controller;
use App\Repositories\Api\V1\StarCitizen\Interfaces\Stats\StatsRepositoryInterface;
use InvalidArgumentException;

/**
 * Class StatsAPIController
 */
class StatController extends Controller
{
    /**
     * StatsRepository
     *
     * @var \App\Repositories\StarCitizen\Api\v1\Stats\StatsRepository
     */
    private $repository;

    /**
     * StatsAPIController constructor.
     *
     * @param \App\Repositories\Api\V1\StarCitizen\Interfaces\Stats\StatsRepositoryInterface $repository
     */
    public function __construct(StatsRepositoryInterface $repository)
    {
        parent::__construct();
        $this->repository = $repository;
    }

    /**
     * @param string $method
     * @param array  $parameters
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function __call($method, $parameters)
    {
        if (method_exists($this->repository, $method) && is_callable([$this->repository, $method])) {
            /** @var \App\Http\Resources\Api\V1\StarCitizen\Stat\Stat $return */
            $return = call_user_func_array([$this->repository, $method], []);

            return $return;
        }

        throw new InvalidArgumentException("Method {$method} does not exist in Repository!");
    }
}
