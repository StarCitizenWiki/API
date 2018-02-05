<?php declare(strict_types = 1);

namespace App\Repositories\StarCitizenDB;

use App\Exceptions\MethodNotImplementedException;
use App\Repositories\AbstractBaseRepository;
use App\Repositories\StarCitizenWiki\Interfaces\ShipsRepositoryInterface;
use App\Transformers\NullTransformer as ShipsTransformer;
use App\Transformers\StarCitizenDB\Ships\ShipsListTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

/**
 * Class ShipsRepository
 *
 * @package App\Repositories\StarCitizenDB
 */
class ShipsRepository extends AbstractBaseRepository implements ShipsRepositoryInterface
{
    /**
     * @return \App\Repositories\StarCitizenDB\ShipsRepository
     * @throws \App\Exceptions\WrongMethodNameException
     */
    public function getShipList(): ShipsRepository
    {
        app('Log')::info(make_name_readable(__FUNCTION__));
        $this->collection()->withTransformer(ShipsListTransformer::class);
        $this->dataToTransform = File::allFiles(config('filesystems.disks.scdb_ships_splitted.root'));

        return $this;
    }

    /**
     * Returns Ship data
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $shipName ShipName
     *
     * @return ShipsRepositoryInterface
     * @throws \App\Exceptions\InvalidDataException
     * @throws \App\Exceptions\WrongMethodNameException
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function getShip(Request $request, string $shipName)
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
        $this->withTransformer(ShipsTransformer::class);
        $this->getTransformer()->addFilters($request);

        return $this;
    }

    /**
     * Not Implemented
     *
     * @param string $shipName ShipName
     *
     * @return void
     *
     * @throws \App\Exceptions\MethodNotImplementedException
     */
    public function searchShips(string $shipName)
    {
        throw new MethodNotImplementedException('Can\'t currently search for scdb ships');
    }

    /**
     * Checks if the Response Data is valid, must be overridden
     *
     * @return bool
     */
    protected function checkIfResponseDataIsValid(): bool
    {
        // TODO: Implement checkIfResponseDataIsValid() method.
        return true;
    }
}
