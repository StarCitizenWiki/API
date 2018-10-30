<?php declare(strict_types = 1);
/**
 * Created by PhpStorm.
 * User: Hanne
 * Date: 09.08.2018
 * Time: 16:13
 */

namespace App\Transformers\Api\V1\StarCitizen\Manufacturer;

use App\Models\Api\StarCitizen\Manufacturer\Manufacturer;
use App\Models\System\Translation\AbstractHasTranslations;
use App\Transformers\Api\LocaleAwareTransformerInterface;
use App\Transformers\Api\V1\StarCitizen\Vehicle\GroundVehicle\GroundVehicleLinkTransformer;
use App\Transformers\Api\V1\StarCitizen\Vehicle\Ship\ShipLinkTransformer;
use League\Fractal\TransformerAbstract;

/**
 * Manufacturer Transformer
 */
class ManufacturerTransformer extends TransformerAbstract implements LocaleAwareTransformerInterface
{
    protected $availableIncludes = [
        'ships',
        'vehicles',
    ];

    private $localeCode;

    /**
     * Set the Locale
     *
     * @param string $localeCode
     */
    public function setLocale(string $localeCode)
    {
        $this->localeCode = $localeCode;
    }

    /**
     * @param \App\Models\Api\StarCitizen\Manufacturer\Manufacturer $manufacturer
     *
     * @return array
     */
    public function transform(Manufacturer $manufacturer)
    {
        $translations = $this->getTranslation($manufacturer);

        return [
            'code' => $manufacturer->name_short,
            'name' => $manufacturer->name,
            'known_for' => $translations['known_for'],
            'description' => $translations['description'],
        ];
    }

    /**
     * @param \App\Models\Api\StarCitizen\Manufacturer\Manufacturer $manufacturer
     *
     * @return \League\Fractal\Resource\Collection
     */
    public function includeShips(Manufacturer $manufacturer)
    {
        return $this->collection($manufacturer->ships, new ShipLinkTransformer());
    }

    /**
     * @param \App\Models\Api\StarCitizen\Manufacturer\Manufacturer $manufacturer
     *
     * @return \League\Fractal\Resource\Collection
     */
    public function includeVehicles(Manufacturer $manufacturer)
    {
        return $this->collection($manufacturer->vehicles, new GroundVehicleLinkTransformer());
    }

    /**
     * If a valid locale code is set this function will return the corresponding translation or use english as a fallback
     * @Todo Generate Array with translations that used the english fallback
     *
     * @param \App\Models\System\Translation\AbstractHasTranslations $model
     *
     * @return array|string the Translation
     */
    private function getTranslation(AbstractHasTranslations $model)
    {
        app('Log')::debug(
            "Relation translations for Model ".get_class($model)." is loaded: {$model->relationLoaded('translations')}"
        );

        $translations = [
            'known_for' => [],
            'description' => [],
        ];

        $model->translations->each(
            function ($translation) use (&$translations) {
                if (null !== $this->localeCode) {
                    if ($translation->locale_code === $this->localeCode || (empty($translations['known_for']) && $translation->locale_code === config(
                                'language.english'
                            ))) {
                        $translations = [
                            'known_for' => $translation->known_for,
                            'description' => $translation->description,
                        ];
                    } else {
                        // Translation already found, exit loop
                        return false;
                    }


                    return $translation;
                } else {
                    $translations['known_for'][$translation->locale_code] = $translation->known_for;
                    $translations['description'][$translation->locale_code] = $translation->description;
                }
            }
        );

        return $translations;
    }
}
