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
    public function get(string $name);

    public function getAll();
}
