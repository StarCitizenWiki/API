<?php declare(strict_types = 1);

namespace App\Http\Controllers\Api\V1\StarCitizen\Vehicle\Ship;

use App\Http\Controllers\Controller;
use App\Repositories\Api\V1\StarCitizen\Interfaces\Vehicle\Ship\ShipRepositoryInterface;

/**
 * Class ShipController
 *
 * @Resource('Ship')
 */
class ShipController extends Controller
{
    /**
     * @var \App\Repositories\Api\V1\StarCitizen\Interfaces\Vehicle\Ship\ShipRepositoryInterface
     */
    private $repository;

    public function __construct(ShipRepositoryInterface $shipRepository)
    {
        $this->repository = $shipRepository;
    }

    public function show(string $shipName)
    {
        return $this->repository->get($shipName);
    }

    public function getAll()
    {
        return $this->repository->getAll();
    }
}
