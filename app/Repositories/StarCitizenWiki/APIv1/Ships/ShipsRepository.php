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
use App\Transformers\StarCitizenWiki\SMWTransformer;
use Illuminate\Support\Facades\Log;
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
     * @param String $shipName ShipName
     *
     * @return ShipsRepository
     */
    public function getShip(String $shipName) : ShipsRepository
    {
        $shipName = urldecode($shipName);
        Log::debug('Getting Ship by name', [
            'method' => __METHOD__,
            'ship' => $shipName,
        ]);
        $this->transformer = resolve(SMWTransformer::class);
        $this->request(
            'GET',
            '?action=browsebysubject&format=json&utf8=1&subject='.$shipName,
            []
        );
        $smwData = $this->asArray()['data'];

        $smwData[$shipName] = ['subject' => $smwData['subject']] + $smwData[str_replace('_', ' ', $shipName)];

        $this->dataToTransform = [
            'wiki' => $smwData[$shipName],
        ];
        $this->transformedResource = null;
        $this->transformer = resolve(ShipsTransformer::class);

        if (isset($this->dataToTransform['wiki']['subject'])) {
            $subject = explode('/', $this->dataToTransform['wiki']['subject']);
            if (count($subject) === 3) {
                $shipName = str_replace(['-', ' '], '_', $shipName);
                $fileName = $subject[1].'_'.$shipName.'.json';

                Log::debug('Checking if StarCitizenDB Data exists for ship', [
                    'method' => __METHOD__,
                    'filename' => $fileName,
                ]);
                if (Storage::disk('scdb_ships_splitted')->exists($fileName)) {
                    Log::debug('File exists adding content to transformation', [
                        'method' => __METHOD__,
                    ]);
                    $content = Storage::disk('scdb_ships_splitted')->get($fileName);
                    $this->dataToTransform['scdb'] = json_decode($content, true);
                }
            }
        }

        return $this;
    }

    /**
     * Gets a ShipList
     *
     * @return ShipsRepository
     */
    public function getShipList() : ShipsRepository
    {
        Log::debug('Getting ShipList', [
            'method' => __METHOD__,
        ]);
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
                Log::debug('Getting Data for next offset', [
                    'method' => __METHOD__,
                    'offset' => $offset,
                ]);
            }
        } while (array_key_exists('query-continue-offset', $response));

        Log::debug('Finished getting Data from Wiki', [
            'method' => __METHOD__,
        ]);
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
        Log::debug('Searching for Ship', [
            'method' => __METHOD__,
            'name' => $shipName,
        ]);
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
        Log::debug('Finished getting Data from Wiki', [
            'method' => __METHOD__,
        ]);

        return $this;
    }
}