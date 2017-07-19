<?php
/**
 * User: Hannes
 * Date: 03.03.2017
 * Time: 18:16
 */

namespace App\Repositories\StarCitizenWiki\APIv1;

use App\Repositories\StarCitizenWiki\BaseStarCitizenWikiAPI;
use App\Repositories\StarCitizenWiki\Interfaces\ShipsInterface;
use App\Traits\ProfilesMethodsTrait;
use App\Transformers\StarCitizenWiki\Ships\ShipsListTransformer;
use App\Transformers\StarCitizenWiki\Ships\ShipsSearchTransformer;
use App\Transformers\StarCitizenWiki\Ships\ShipsTransformer;
use App\Transformers\StarCitizenWiki\SMWTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Class ShipsRepository
 * @package App\Repositories\StarCitizenWiki\APIv1\Ships
 */
class ShipsRepository extends BaseStarCitizenWikiAPI implements ShipsInterface
{
    /**
     * Returns Ship data
     *
     * @param Request $request
     * @param String  $shipName ShipName
     *
     * @return ShipsRepository
     */
    public function getShip(Request $request, String $shipName) : ShipsRepository
    {
        $shipName = urldecode($shipName);
        app('Log')::info(make_name_readable(__FUNCTION__), ['ship' => $shipName]);

        $this->getShipDataFromWiki($shipName);
        $this->resetTransform();
        $this->transformer = resolve(ShipsTransformer::class);
        $this->transformer->addFilters($request);
        $this->getShipDataFromSCDB();

        return $this;
    }

    /**
     * Gets a ShipList
     *
     * @return ShipsRepository
     */
    public function getShipList() : ShipsRepository
    {
        app('Log')::info(make_name_readable(__FUNCTION__));
        $this->collection();
        $this->transformer = resolve(ShipsListTransformer::class);

        $offset = 0;
        $data = [];
        do {
            $response = (String) $this->request(
                'GET',
                '?action=askargs&format=json&conditions=Kategorie%3ARaumschiff%7CHersteller%3A%3A%2B&parameters=offset%3D'.$offset,
                []
            )->getBody();
            $response = json_decode($response, true);
            $data = array_merge($data, $response['query']['results']);
            if (array_key_exists('query-continue-offset', $response)) {
                $offset = $response['query-continue-offset'];
            }
        } while (array_key_exists('query-continue-offset', $response));

        $this->dataToTransform = $data;

        return $this;
    }

    /**
     * Seraches for a Ship
     *
     * @param String $shipName ShipName
     *
     * @return ShipsRepository
     */
    public function searchShips(String $shipName)
    {
        app('Log')::info(make_name_readable(__FUNCTION__), ['ship' => $shipName]);
        /**
         * TODO: Suche Gibt teils Mist zurück
         * Beispiel: Suche nach Aurora gibt zusätzlich Orion und Hull A zurück!?
         */
        $this->transformer = resolve(ShipsSearchTransformer::class);
        $this->collection()->request(
            'GET',
            '?action=query&format=json&list=search&continue=-%7C%7Ccategories%7Ccategoryinfo&srnamespace=0&srprop=&srsearch=-intitle:Hersteller+incategory%3ARaumschiff+'.$shipName,
            []
        );
        $this->dataToTransform = $this->dataToTransform['query']['search'];

        return $this;
    }

    /**
     * Loads SMW Data by ship name
     *
     * @param String $shipName
     */
    private function getShipDataFromWiki(String $shipName) : void
    {
        $this->transformer = resolve(SMWTransformer::class);
        $this->request(
            'GET',
            '?action=browsebysubject&format=json&utf8=1&subject='.$shipName,
            []
        );
        $smwData = $this->asArray()['data'];

        $altIndex = last(explode('/', $smwData['subject']));
        $altIndex = str_replace('_', ' ', $altIndex);

        $this->dataToTransform = [
            'wiki' => [
                'subject' => $smwData['subject'],
                'data' => $smwData[$smwData['subject']] ?? $smwData[$altIndex],
            ],
        ];
    }

    /**
     * Loads SCDB Data from file
     */
    private function getShipDataFromSCDB() : void
    {
        if (isset($this->dataToTransform['wiki']['subject'])) {
            $content = '';

            $subject = explode('/', $this->dataToTransform['wiki']['subject']);
            if (count($subject) === 3) {
                $shipName = last($subject);
                $fileName = strtolower($subject[1].'_'.$shipName.'.json');

                if (Storage::disk('scdb_ships_splitted')->exists($fileName)) {
                    $content = Storage::disk('scdb_ships_splitted')->get($fileName);
                }
            }
            $this->dataToTransform['scdb'] = json_decode($content, true);
        }
    }

    /**
     * Resets the transformer and transformedResource to null
     */
    private function resetTransform() : void
    {
        $this->transformer = null;
        $this->transformedResource = null;
    }
}
