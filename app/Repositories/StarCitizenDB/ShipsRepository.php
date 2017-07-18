<?php
namespace App\Repositories\StarCitizenDB;

use App\Exceptions\MethodNotImplementedException;
use App\Repositories\BaseAPITrait;
use App\Repositories\StarCitizenWiki\Interfaces\ShipsInterface;
use App\Traits\TransformesDataTrait;
use App\Transformers\FakeTransformer as ShipsTransformer;
use App\Transformers\StarCitizenDB\Ships\ShipsListTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

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
        app('Log')::info(make_name_readable(__FUNCTION__));
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
        app('Log')::info(make_name_readable(__FUNCTION__), ['ship' => $shipName]);

        $shipName = str_replace(' ', '_', $shipName).'.json';
        $shipName = strtolower($shipName);

        $content = '';
        if (Storage::disk('scdb_ships_splitted')->exists($shipName)) {
            $content = Storage::disk('scdb_ships_splitted')->get($shipName);
        }

        $this->dataToTransform = json_decode($content, true);
        unset($this->dataToTransform['processedName']);
        unset($this->dataToTransform['filename']);
        $this->transformer = resolve(ShipsTransformer::class);
        $this->transformer->addFilters($request);

        return $this;
    }

    /**
     * Not Implemented
     *
     * @param String $shipName ShipName
     *
     * @return ShipsInterface
     * @throws MethodNotImplementedException
     */
    public function searchShips(String $shipName)
    {
        throw new MethodNotImplementedException('Can\'t currently search for scdb ships');
    }
}
