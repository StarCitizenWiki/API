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
use League\Fractal\TransformerAbstract;

/**
 * Manufacturer Transformer
 */
class ManufacturerTransformer extends TransformerAbstract implements LocaleAwareTransformerInterface
{
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

        $manufacturerTransformed = [
            'code' => $manufacturer->name_short,
            'name' => $manufacturer->name,
            'known_for' => $translations['known_for'],
            'description' => $translations['description'],
        ];

        if ($manufacturer->relationLoaded('ships')) {
            $manufacturerTransformed['ships'] = $this->getShipLinksForManufacturer($manufacturer);
        }

        if ($manufacturer->relationLoaded('groundVehicles')) {
            $manufacturerTransformed['ground_vehicles'] = $this->getGroundVehicleLinksForManufacturer($manufacturer);
        }

        return $manufacturerTransformed;
    }

    /**
     * Links Ships to Api Endpoints
     *
     * @param \App\Models\Api\StarCitizen\Manufacturer\Manufacturer $manufacturer
     *
     * @return array
     */
    private function getShipLinksForManufacturer(Manufacturer $manufacturer): array
    {
        $ships = [];

        foreach ($manufacturer->ships as $ship) {
            $ships[] = app('api.url')->version('v1')->route(
                'api.v1.starcitizen.vehicles.ships.show',
                [$ship->getRouteKey()]
            );
        }

        return $ships;
    }

    /**
     * Links Ground Vehicles to Api Endpoints
     *
     * @param \App\Models\Api\StarCitizen\Manufacturer\Manufacturer $manufacturer
     *
     * @return array
     */
    private function getGroundVehicleLinksForManufacturer(Manufacturer $manufacturer): array
    {
        $groundVehicles = [];

        foreach ($manufacturer->groundVehicles as $groundVehicle) {
            $groundVehicles[] = app('api.url')->version('v1')->route(
                'api.v1.starcitizen.vehicles.ground_vehicles.show',
                [$groundVehicle->getRouteKey()]
            );
        }

        return $groundVehicles;
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
                    if ($translation->locale_code === $this->localeCode || (empty($translations['known_for']) && $translation->locale_code === config('language.english'))) {
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
