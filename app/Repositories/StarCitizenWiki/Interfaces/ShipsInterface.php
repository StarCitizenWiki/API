<?php
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
     * @param Request $request
     * @param String  $shipName ShipName
     *
     * @return ShipsInterface
     */
    public function getShip(Request $request, String $shipName);

    /**
     * Gets a ShipList
     *
     * @return ShipsInterface
     */
    public function getShipList();

    /**
     * Searches for a Ship
     *
     * @param String $shipName ShipName
     *
     * @return ShipsInterface
     */
    public function searchShips(String $shipName);
}
