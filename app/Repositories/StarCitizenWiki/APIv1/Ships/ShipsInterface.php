<?php
/**
 * Created by PhpStorm.
 * User: Hanne
 * Date: 01.02.2017
 * Time: 22:59
 */

namespace App\Repositories\StarCitizenWiki\APIv1\Ships;

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
     * @return ShipsRepository
     */
    public function getShip(Request $request, String $shipName);

    /**
     * Gets a ShipList
     *
     * @return ShipsRepository
     */
    public function getShipList();

    /**
     * Seraches for a Ship
     *
     * @param String $shipName ShipName
     *
     * @return ShipsRepository
     */
    public function searchShips(String $shipName);
}
