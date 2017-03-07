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
     * @return ShipsRepository
     */
    public function getShip(String $shipName)
    {
        // TODO: Implement getShip() method.
        return $this;
    }

    /**
     * @return ShipsRepository
     */
    public function getShipList() : ShipsRepository
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

        $this->_responseBody = $data;
        return $this;
    }

    /**
     * @param String $shipName
     * @return ShipsRepository
     */
    public function searchShips(String $shipName)
    {
        // TODO: Implement searchShips() method.
        return $this;
    }
}