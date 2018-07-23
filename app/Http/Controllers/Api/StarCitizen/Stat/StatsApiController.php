<?php declare(strict_types = 1);

namespace App\Http\Controllers\Api\StarCitizen\Stat;

use App\Http\Controllers\Controller;
use App\Repositories\StarCitizen\Interfaces\Stats\StatsRepositoryInterface;
use InvalidArgumentException;

/**
 * Class StatsAPIController
 */
class StatsApiController extends Controller
{
    /**
     * StatsRepository
     *
     * @var \App\Repositories\StarCitizen\ApiV1\StatsRepository
     */
    private $repository;

    /**
     * StatsAPIController constructor.
     *
     * @param \App\Repositories\StarCitizen\Interfaces\Stats\StatsRepositoryInterface $repository
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
            /** @var \Spatie\Fractal\Fractal $return */
            $return = call_user_func_array([$this->repository, $method], []);

            return $return->respond(200, [], JSON_PRETTY_PRINT);
        }

        throw new InvalidArgumentException("Method {$method} does not exist in Repository!");
    }
}
