<?php
/**
 * Created by PhpStorm.
 * User: Hanne
 * Date: 01.02.2017
 * Time: 22:59
 */

namespace App\Repositories\StarCitizenWiki\APIv1\Ships;

interface ShipsInterface
{
    /**
     * @param String $shipName
     * @return ShipsRepository
     */
    public function getShip(String $shipName);

    /**
     * @return ShipsRepository
     */
    public function getShipList();

    /**
     * @param String $shipName
     * @return ShipsRepository
     */
    public function searchShips(String $shipName);
}