<?php
namespace App\Repositories\StarCitizenDB;

use App\Repositories\BaseAPITrait;
use App\Repositories\StarCitizenWiki\Interfaces\ShipsInterface;
use App\Traits\TransformesDataTrait;
use App\Transformers\StarCitizenDB\Ships\ShipsListTransformer;
use App\Transformers\StarCitizenDB\Ships\FakeTransformer as ShipsTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Spatie\Fractal\Fractal;

/**
 * Class ShipsRepository
 * @package App\Repositories\StarCitizenDB
 */
class ShipsRepository implements ShipsInterface
{
    private const API_URL = '';

    use BaseAPITrait, TransformesDataTrait {
        BaseAPITrait::addMetadataToTransformation insteadof TransformesDataTrait;
    }

    /**
     * @return ShipsRepository
     */
    public function getShipList() : ShipsRepository
    {
        Log::debug('Getting ShipList', [
            'method' => __METHOD__,
        ]);
        $this->collection()->withTransformer(ShipsListTransformer::class);
        $this->dataToTransform = File::allFiles(config('filesystems.disks.scdb_ships_splitted.root'));

        return $this;
    }

    /**
     * Returns Ship data
     *
     * @param Request $request
     * @param String  $shipName ShipName
     *
     * @return ShipsInterface
     */
    public function getShip(Request $request, String $shipName)
    {
        $shipName = urldecode($shipName);
        Log::debug('Getting Ship by name', [
            'method' => __METHOD__,
            'ship' => $shipName,
        ]);

        $shipName = str_replace(' ', '_', $shipName).'.json';
        $shipName = strtolower($shipName);

        $content = '';
        if (Storage::disk('scdb_ships_splitted')->exists($shipName)) {
            $content = Storage::disk('scdb_ships_splitted')->get($shipName);
        }

        $this->dataToTransform = json_decode($content, true);
        $this->transformer = resolve(ShipsTransformer::class);
        $this->transformer->addFilters($request);

        return $this;
    }

    /**
     * Seraches for a Ship
     *
     * @param String $shipName ShipName
     *
     * @return ShipsInterface
     */
    public function searchShips(String $shipName)
    {
        // TODO: Implement searchShips() method.
    }
}