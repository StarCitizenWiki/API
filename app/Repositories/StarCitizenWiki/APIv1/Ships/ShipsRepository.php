<?php
/**
 * User: Hannes
 * Date: 03.03.2017
 * Time: 18:16
 */

namespace App\Repositories\StarCitizenWiki\APIv1\Ships;

use App\Repositories\StarCitizenWiki\APIv1\BaseStarCitizenWikiAPI;
use App\Transformers\StarCitizenWiki\Ships\ShipsListTransformer;
use App\Transformers\StarCitizenWiki\Ships\ShipsSearchTransformer;
use App\Transformers\StarCitizenWiki\Ships\ShipsTransformer;

class ShipsRepository extends BaseStarCitizenWikiAPI implements ShipsInterface
{
    /**
     * @param String $shipName
     * @return ShipsRepository
     */
    public function getShip(String $shipName) : ShipsRepository
    {
        $this->_transformer = resolve(ShipsTransformer::class);
        $this->request('GET', '?action=browsebysubject&format=json&subject='.$shipName, []);
        return $this;
    }

    /**
     * @return ShipsRepository
     */
    public function getShipList() : ShipsRepository
    {
        $this->collection();
        $this->_transformer = resolve(ShipsListTransformer::class);
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
        /**
         * TODO: Suche Gibt teils Mist zurück
         * Beispiel: Suche nach Aurora gibt zusätzlich Orion und Hull A zurück!?
         */
        $this->_transformer = resolve(ShipsSearchTransformer::class);
        $this->collection()->request('GET', '/api.php?action=query&format=json&list=search&continue=-%7C%7Ccategories%7Ccategoryinfo&srnamespace=0&srprop=&srsearch=-intitle:Hersteller+incategory%3ARaumschiff+'.$shipName, []);
        $this->_responseBody = $this->_responseBody['query']['search'];
        return $this;
    }
}