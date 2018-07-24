<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 24.07.2018
 * Time: 20:20
 */

namespace App\Repositories\Api\V1\StarCitizen\Vehicle\Ship;

use App\Models\Api\StarCitizen\Vehicle\Ship\Ship;
use App\Repositories\AbstractBaseRepository as BaseRepository;
use App\Repositories\Api\V1\StarCitizen\Interfaces\Vehicle\Ship\ShipRepositoryInterface;
use App\Transformers\Api\V1\StarCitizen\Vehicle\Ship\ShipTransformer;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class ShipsRepository
 */
class ShipRepository extends BaseRepository implements ShipRepositoryInterface
{
    public function getAll()
    {
        $ships = Ship::paginate(5);

        return $this->response->paginator($ships, new ShipTransformer());
    }

    public function get(string $shipName)
    {
        $shipName = str_replace('_', ' ', $shipName);
        try {
            $ship = Ship::where('name', $shipName)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            throw new NotFoundHttpException(sprintf('No Ship found for Query: %s', $shipName));
        }

        return $this->response->item($ship, new ShipTransformer());
    }
}
