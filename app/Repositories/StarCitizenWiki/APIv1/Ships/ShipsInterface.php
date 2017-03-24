<?php
/**
 * Created by PhpStorm.
 * User: Hanne
 * Date: 01.02.2017
 * Time: 22:59
 */

namespace App\Repositories\StarCitizenWiki\APIv1\Ships;

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
     * @param String $shipName ShipName
     *
     * @return ShipsRepository
     */
    public function getShip(String $shipName);

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