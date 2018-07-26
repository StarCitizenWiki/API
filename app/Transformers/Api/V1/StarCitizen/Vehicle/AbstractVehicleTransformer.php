<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 24.07.2018
 * Time: 21:23
 */

namespace App\Transformers\Api\V1\StarCitizen\Vehicle;

use App\Models\Api\StarCitizen\Vehicle\AbstractVehicle as Vehicle;
use App\Models\Api\Translation\AbstractHasTranslations as HasTranslations;
use App\Transformers\Api\LocaleAwareTransformerInterface as LocaleAwareTransformer;
use League\Fractal\TransformerAbstract;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class AbstractVehicleTransformer
 */
abstract class AbstractVehicleTransformer extends TransformerAbstract implements LocaleAwareTransformer
{
    private $localeCode;

    /**
     * Set the Locale
     *
     * @param string $localeCode
     */
    public function setLocale(string $localeCode)
    {
        if (!in_array($localeCode, config('language.codes'))) {
            throw new BadRequestHttpException(sprintf("Locale Code %s is not valid", $localeCode));
        }

        $this->localeCode = $localeCode;
    }

    /**
     * @param \App\Models\Api\StarCitizen\Vehicle\AbstractVehicle $vehicle
     *
     * @return array
     */
    protected function getFociTranslations(Vehicle $vehicle)
    {
        /** @var \Illuminate\Support\Collection $foci */
        $foci = $vehicle->foci;
        $fociTranslations = [];

        $foci->each(
            function ($vehicleFocus) use (&$fociTranslations) {
                $fociTranslations[] = $this->getTranslation($vehicleFocus);
            }
        );

        return $fociTranslations;
    }

    /**
     * @param \App\Models\Api\StarCitizen\Vehicle\AbstractVehicle $vehicle
     *
     * @return array
     */
    protected function getProductionStatusTranslations(Vehicle $vehicle)
    {
        return $this->getTranslation($vehicle->productionStatus);
    }

    /**
     * @param \App\Models\Api\StarCitizen\Vehicle\AbstractVehicle $vehicle
     *
     * @return array
     */
    protected function getProductionNoteTranslations(Vehicle $vehicle)
    {
        return $this->getTranslation($vehicle->productionNote);
    }

    /**
     * @param \App\Models\Api\StarCitizen\Vehicle\AbstractVehicle $vehicle
     *
     * @return array
     */
    protected function getDescriptionTranslations(Vehicle $vehicle)
    {
        return $this->getTranslation($vehicle);
    }

    /**
     * @param \App\Models\Api\StarCitizen\Vehicle\AbstractVehicle $vehicle
     *
     * @return array
     */
    protected function getTypeTranslations(Vehicle $vehicle)
    {
        return $this->getTranslation($vehicle->type);
    }

    /**
     * @param \App\Models\Api\StarCitizen\Vehicle\AbstractVehicle $vehicle
     *
     * @return array
     */
    protected function getSizeTranslations(Vehicle $vehicle)
    {
        return $this->getTranslation($vehicle->size);
    }

    /**
     * If a valid locale code is set this function will return the corresponding translation or use english as a fallback
     * @Todo Generate Array with translations that used the english fallback
     *
     * @param \App\Models\Api\Translation\AbstractHasTranslations $model
     *
     * @return array|string the Translation
     */
    private function getTranslation(HasTranslations $model)
    {
        app('Log')::debug(
            "Relation translations for Model ".get_class($model)." is loaded: {$model->relationLoaded('translations')}"
        );

        $translations = [];

        $model->translations->each(
            function ($translation) use (&$translations) {
                if (null !== $this->localeCode) {
                    if ($translation->locale_code === $this->localeCode ||
                        (empty($translations) && $translation->locale_code === config('language.english'))) {
                        $translations = $translation->translation;
                    } else {
                        // Translation already found, exit loop
                        return false;
                    }

                    return $translation;
                } else {
                    $translations[$translation->locale_code] = $translation->translation;
                }
            }
        );

        return $translations;
    }
}
