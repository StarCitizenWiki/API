<?php declare(strict_types = 1);

namespace App\Repositories\StarCitizenWiki\Interfaces;

use Illuminate\Http\Request;

/**
 * Interface ShipsInterface
 *
 * @package App\Repositories\StarCitizenWiki\APIv1\Ships
 */
interface ShipsInterface
{
    /**
     * Returns Ship data
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $shipName ShipName
     *
     * @return ShipsInterface
     */
    public function getShip(Request $request, string $shipName);

    /**
     * Gets a ShipList
     *
     * @return ShipsInterface
     */
    public function getShipList();

    /**
     * Searches for a Ship
     *
     * @param string $shipName ShipName
     *
     * @return ShipsInterface
     */
    public function searchShips(string $shipName);
}
