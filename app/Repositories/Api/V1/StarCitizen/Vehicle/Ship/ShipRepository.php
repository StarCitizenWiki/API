<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 24.07.2018
 * Time: 20:20
 */

namespace App\Repositories\Api\V1\StarCitizen\Vehicle\Ship;

use App\Models\Api\StarCitizen\Vehicle\Ship\Ship;
use App\Models\System\Language;
use App\Repositories\AbstractBaseRepository as BaseRepository;
use App\Repositories\Api\V1\StarCitizen\Interfaces\Vehicle\Ship\ShipRepositoryInterface;
use App\Transformers\Api\V1\StarCitizen\Vehicle\Ship\ShipTransformer;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Class ShipsRepository
 */
class ShipRepository extends BaseRepository implements ShipRepositoryInterface
{
    /**
     * @var \App\Transformers\Api\V1\StarCitizen\Vehicle\Ship\ShipTransformer
     */
    private $transformer;

    /**
     * ShipRepository constructor.
     * @param \App\Transformers\Api\V1\StarCitizen\Vehicle\Ship\ShipTransformer $transformer
     */
    public function __construct(ShipTransformer $transformer)
    {
        $this->transformer = $transformer;
    }

    public function setLocale(string $locale)
    {
        try {
            Language::where('locale_code', $locale)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            $this->response->errorBadRequest(sprintf("Locale Code %s is not valid!", $locale));
        }

        $this->transformer->setLocale($locale);
    }

    /**
     * Return all Ships paginated
     *
     * @return \Dingo\Api\Http\Response
     */
    public function all()
    {
        $ships = Ship::paginate();

        return $this->response->paginator($ships, $this->transformer);
    }

    /**
     * Display a Ship by Name
     *
     * @param string $shipName The Ship Name
     *
     * @return \Dingo\Api\Http\Response
     */
    public function show(string $shipName)
    {
        $shipName = str_replace('_', ' ', $shipName);
        try {
            $ship = Ship::where('name', $shipName)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            $this->response->errorNotFound(sprintf('No Ship found for Query: %s', $shipName));
        }

        return $this->response->item($ship, $this->transformer);
    }
}
