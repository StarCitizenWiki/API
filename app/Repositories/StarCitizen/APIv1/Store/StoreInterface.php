<?php
/**
 * User: Hannes
 * Date: 19.01.2017
 * Time: 12:45
 */

namespace app\Repositories\StarCitizen\APIv1\Store;


interface StoreInterface
{
    /**
     * https://robertsspaceindustries.com/api/store/getShips
     * {"sort":"store","search":"","itemType":"ships","storefront":"pledge","type":"","classification":[],"mass":"","manufacturer_id":[],"length":"","maxcrew":"","msrp":"","page":2}
     * @return string json
     */
    public function getShips();

    /**
     * https://robertsspaceindustries.com/api/store/getProducts
     * {"categories":[],"sort":"store","search":"","itemType":"products","storefront":"pledge","type":"merchandise","page":2}
     * @return string json
     */
    public function getProducts();

    /**
     * https://robertsspaceindustries.com/api/store/getSKUs
     * @return string json
     */
    public function getSKUs();

    /**
     * https://robertsspaceindustries.com/api/store/getShipSuggestedSKU
     * ship_id : 100
     * storefront : "pledge"
     * @param Integer $shipID
     * @param String $storeFront
     * @return string json
     */
    public function getShipSuggestedSKU(Integer $shipID, String $storeFront);

    /**
     * https://robertsspaceindustries.com/api/store/getShipUpgradeSKU
     * {from_ship_id: "22", to_ship_id: "16", storefront: "pledge"}
     * @return string json
     */
    public function getShipUpgradeSKU();
}