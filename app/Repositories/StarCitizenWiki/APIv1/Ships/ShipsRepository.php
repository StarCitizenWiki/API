<?php
/**
 * User: Hannes
 * Date: 03.03.2017
 * Time: 18:16
 */

namespace App\Repositories\StarCitizenWiki\APIv1\Ships;

use App\Repositories\StarCitizenWiki\APIv1\BaseStarCitizenWikiAPI;
use App\Transformers\StarCitizenWiki\ShipsTransformer;

class ShipsRepository extends BaseStarCitizenWikiAPI implements ShipsInterface
{

    public function __construct(ShipsTransformer $transformer)
    {
        $this->_transformer = $transformer;
        parent::__construct();
    }

    /**
     * @param String $shipName
     * @return \GuzzleHttp\Psr7\Response
     */
    public function getShip(String $shipName)
    {
        // TODO: Implement getShip() method.
    }

    /**
     * @return String
     *
     */
    public function getShipList()
    {
        $offset = 0;
        $data = [];
        do {
            $response = (String) $this->request('GET', '?action=askargs&format=json&conditions=Kategorie%3ARaumschiff%7CHersteller%3A%3A%2B&parameters=offset%3D'.$offset, [])->getBody();
            $response = json_decode($response, true);
            $data = array_merge($data, $response['query']['results']);
            if (array_key_exists('query-continue-offset', $response)) {
                $offset = $response['query-continue-offset'];
            }
        } while (array_key_exists('query-continue-offset', $response));

        return json_encode($data, JSON_PRETTY_PRINT);
    }

    /**
     * @param String $shipName
     * @return \GuzzleHttp\Psr7\Response
     */
    public function searchShips(String $shipName)
    {
        // TODO: Implement searchShips() method.
    }
}