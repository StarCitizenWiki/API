<?php
/**
 * User: Hannes
 * Date: 19.01.2017
 * Time: 12:47
 */

namespace app\Repositories\StarCitizen\APIv1\Hub;


interface HubInterface
{
    /**
     * @TODO Funktion noch nicht weiter verfolgt
     * https://robertsspaceindustries.com/api/hub/getSeries
     * {channel: "engineering"}
     * @return string json
     */
    public function getSeries();

    /**
     * @TODO Funktion noch nicht weiter verfolgt
     * https://robertsspaceindustries.com/api/hub/getCommlinkItems
     * {channel: "engineering", series: "", type: "", text: "", sort: "publish_new", page: 2}
     * @return string json
     */
    public function getCommlinkItems();
}