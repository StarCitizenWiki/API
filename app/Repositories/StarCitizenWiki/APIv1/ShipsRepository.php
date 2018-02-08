<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 03.03.2017
 * Time: 18:16
 */

namespace App\Repositories\StarCitizenWiki\ApiV1;

use App\Repositories\StarCitizenWiki\AbstractStarCitizenWikiRepository;
use App\Repositories\StarCitizenWiki\Interfaces\ShipsRepositoryInterface;
use App\Transformers\StarCitizenWiki\Ships\ShipsListTransformer;
use App\Transformers\StarCitizenWiki\Ships\ShipsSearchTransformer;
use App\Transformers\StarCitizenWiki\Ships\ShipsTransformer;
use App\Transformers\StarCitizenWiki\SMWTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Class ShipsRepository
 * @package App\Repositories\StarCitizenWiki\ApiV1\Ships
 */
class ShipsRepository extends AbstractStarCitizenWikiRepository implements ShipsRepositoryInterface
{
    /**
     * Returns Ship data
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $shipName ShipName
     *
     * @return \App\Repositories\StarCitizenWiki\ApiV1\ShipsRepository
     * @throws \App\Exceptions\InvalidDataException
     * @throws \App\Exceptions\WrongMethodNameException
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     * @throws \App\Exceptions\MissingTransformerException
     */
    public function getShip(Request $request, string $shipName): ShipsRepository
    {
        $shipName = urldecode($shipName);
        app('Log')::info(make_name_readable(__FUNCTION__), ['ship' => $shipName]);

        $this->getShipDataFromWiki($shipName);
        $this->resetTransform();
        $this->withTransformer(ShipsTransformer::class);
        $this->getTransformer()->addFilters($request);
        $this->getShipDataFromSCDB();

        return $this;
    }

    /**
     * Gets a ShipList
     *
     * @return \App\Repositories\StarCitizenWiki\ApiV1\ShipsRepository
     * @throws \App\Exceptions\InvalidDataException
     * @throws \App\Exceptions\WrongMethodNameException
     */
    public function getShipList(): ShipsRepository
    {
        app('Log')::info(make_name_readable(__FUNCTION__));
        $this->collection()->withTransformer(ShipsListTransformer::class);

        $offset = 0;
        $data = [];
        do {
            $response = (string) $this->request(
                'GET',
                '/api.php?action=askargs&format=json&conditions=Kategorie%3ARaumschiff%7CHersteller%3A%3A%2B&parameters=offset%3D'.$offset,
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
     * @param string $shipName ShipName
     *
     * @return \App\Repositories\StarCitizenWiki\ApiV1\ShipsRepository
     * @throws \App\Exceptions\InvalidDataException
     * @throws \App\Exceptions\WrongMethodNameException
     */
    public function searchShips(string $shipName)
    {
        app('Log')::info(make_name_readable(__FUNCTION__), ['ship' => $shipName]);
        /**
         * TODO: Suche Gibt teils Mist zurück
         * Beispiel: Suche nach Aurora gibt zusätzlich Orion und Hull A zurück!?
         */
        $this->collection()->withTransformer(ShipsSearchTransformer::class)->request(
            'GET',
            '/api.php?action=query&format=json&list=search&continue=-%7C%7Ccategories%7Ccategoryinfo&srnamespace=0&srprop=&srsearch=-intitle:Hersteller+incategory%3ARaumschiff+'.$shipName,
            []
        );
        $this->dataToTransform = $this->dataToTransform['query']['search'];

        return $this;
    }

    /**
     * Loads SMW Data by ship name
     *
     * @param string $shipName
     *
     * @throws \App\Exceptions\InvalidDataException
     * @throws \App\Exceptions\MissingTransformerException
     */
    private function getShipDataFromWiki(string $shipName): void
    {
        $this->withTransformer(SMWTransformer::class)->request(
            'GET',
            '/api.php?action=browsebysubject&format=json&utf8=1&subject='.$shipName,
            []
        );
        $smwData = $this->toArray()['data'];

        $altIndex = last(explode('/', $smwData['subject']));
        $altIndex = str_replace('_', ' ', $altIndex);

        $this->dataToTransform = [
            'wiki' => [
                'subject' => $smwData['subject'],
                'data'    => $smwData[$smwData['subject']] ?? $smwData[$altIndex],
            ],
        ];
    }

    /**
     * Resets the transformer and transformedResource to null
     */
    private function resetTransform(): void
    {
        $this->transformedResource = null;
    }

    /**
     * Loads SCDB Data from file
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    private function getShipDataFromSCDB(): void
    {
        if (isset($this->dataToTransform['wiki']['subject'])) {
            $content = '';

            $subject = explode('/', $this->dataToTransform['wiki']['subject']);
            if (3 === count($subject)) {
                $shipName = last($subject);
                $fileName = strtolower($subject[1].'_'.$shipName.'.json');

                if (Storage::disk('scdb_ships_splitted')->exists($fileName)) {
                    $content = Storage::disk('scdb_ships_splitted')->get($fileName);
                }
            }
            $this->dataToTransform['scdb'] = json_decode($content, true);
        }
    }
}
