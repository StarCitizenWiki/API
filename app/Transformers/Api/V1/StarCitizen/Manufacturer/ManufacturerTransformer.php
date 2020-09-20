<?php declare(strict_types=1);

namespace App\Transformers\Api\V1\StarCitizen\Manufacturer;

use App\Models\Api\StarCitizen\Manufacturer\Manufacturer;
use App\Models\System\Translation\AbstractHasTranslations as HasTranslations;
use App\Transformers\Api\V1\StarCitizen\AbstractTranslationTransformer as TranslationTransformer;
use App\Transformers\Api\V1\StarCitizen\Vehicle\GroundVehicle\GroundVehicleLinkTransformer;
use App\Transformers\Api\V1\StarCitizen\Vehicle\Ship\ShipLinkTransformer;
use League\Fractal\Resource\Collection;

/**
 * Manufacturer Transformer
 */
class ManufacturerTransformer extends TranslationTransformer
{
    protected $availableIncludes = [
        'ships',
        'vehicles',
    ];

    /**
     * @param Manufacturer $manufacturer
     *
     * @return array
     */
    public function transform(Manufacturer $manufacturer): array
    {
        $this->missingTranslations = [];
        $translations = $this->getTranslation($manufacturer);

        return [
            'code' => $manufacturer->name_short,
            'name' => $manufacturer->name,
            'known_for' => $translations['known_for'],
            'description' => $translations['description'],
            'missing_translations' => $this->missingTranslations,
        ];
    }

    /**
     * If a valid locale code is set this function will return the corresponding translation or use english as a
     * fallback
     *
     * @param HasTranslations $model
     *
     * @param string          $translationKey
     *
     * @return array|string the Translation
     */
    protected function getTranslation(HasTranslations $model, $translationKey = 'translation')
    {
        return parent::getTranslation($model, ['known_for', 'description']);
    }

    /**
     * @param Manufacturer $manufacturer
     *
     * @return Collection
     */
    public function includeShips(Manufacturer $manufacturer): Collection
    {
        return $this->collection($manufacturer->ships, new ShipLinkTransformer());
    }

    /**
     * @param Manufacturer $manufacturer
     *
     * @return Collection
     */
    public function includeVehicles(Manufacturer $manufacturer): Collection
    {
        return $this->collection($manufacturer->vehicles, new GroundVehicleLinkTransformer());
    }
}
