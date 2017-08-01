<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 19.01.2017
 * Time: 12:47
 */

namespace App\Repositories\StarCitizen\Interfaces;

/**
 * Interface HubInterface
 *
 * @package App\Repositories\StarCitizen\APIv1\Hub
 */
interface HubInterface
{
    /**
     * @TODO Funktion noch nicht weiter verfolgt
     * https://robertsspaceindustries.com/api/hub/getSeries
     * {channel: "engineering"}
     *
     * @return \GuzzleHttp\Psr7\Response
     */
    public function getSeries();

    /**
     * @TODO Funktion noch nicht weiter verfolgt
     * https://robertsspaceindustries.com/api/hub/getCommlinkItems
     * {channel: "engineering", series: "", type: "", text: "", sort: "publish_new", page: 2}
     *
     * @return \GuzzleHttp\Psr7\Response
     */
    public function getCommlinkItems();
}
