<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 24.07.2018
 * Time: 20:37
 */

namespace App\Repositories\Api\V1\StarCitizen\Interfaces\Vehicle\Ship;

/**
 * Interface ShipRepositoryInterface
 */
interface ShipRepositoryInterface
{
    /**
     * Return all Ships paginated
     *
     * @return \Dingo\Api\Http\Response
     */
    public function all();

    /**
     * Display a Ship by Name
     *
     * @param string $shipName The Ship Name
     *
     * @return \Dingo\Api\Http\Response
     */
    public function show(string $shipName);
}
