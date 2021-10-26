<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\StarCitizen\Vehicle;

use App\Models\StarCitizen\Vehicle\Vehicle\Vehicle;
use App\Transformers\Api\V1\StarCitizen\AbstractTranslationTransformer as TranslationTransformer;
use App\Transformers\Api\V1\StarCitizenUnpacked\Shop\ShopTransformer;
use Illuminate\Support\Collection;

/**
 * Class AbstractVehicleTransformer
 */
abstract class AbstractVehicleTransformer extends TranslationTransformer
{
    protected $availableIncludes = [
        'components',
        'shops',
    ];

    /**
     * @param Vehicle $vehicle
     *
     * @return \League\Fractal\Resource\Collection
     *
     * TODO Wrap by component_class key
     */
    public function includeComponents(Vehicle $vehicle): \League\Fractal\Resource\Collection
    {
        return $this->collection($vehicle->components, new ComponentTransformer());
    }

    /**
     * @param Vehicle $vehicle
     * @return \League\Fractal\Resource\Collection
     */
    public function includeShops($vehicle): \League\Fractal\Resource\Collection
    {
        return $this->collection($vehicle->unpacked->shops, new ShopTransformer());
    }

    /**
     * @param Vehicle $vehicle
     *
     * @return array
     */
    protected function getFociTranslations(Vehicle $vehicle): array
    {
        /** @var Collection $foci */
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
     * @param Vehicle $vehicle
     *
     * @return array|string
     */
    protected function getProductionStatusTranslations(Vehicle $vehicle)
    {
        return $this->getTranslation($vehicle->productionStatus);
    }

    /**
     * @param Vehicle $vehicle
     *
     * @return array|string
     */
    protected function getProductionNoteTranslations(Vehicle $vehicle)
    {
        return $this->getTranslation($vehicle->productionNote);
    }

    /**
     * @param Vehicle $vehicle
     *
     * @return array|string
     */
    protected function getDescriptionTranslations(Vehicle $vehicle)
    {
        return $this->getTranslation($vehicle);
    }

    /**
     * @param Vehicle $vehicle
     *
     * @return array|string
     */
    protected function getTypeTranslations(Vehicle $vehicle)
    {
        return $this->getTranslation($vehicle->type);
    }

    /**
     * @param Vehicle $vehicle
     *
     * @return array|string
     */
    protected function getSizeTranslations(Vehicle $vehicle)
    {
        return $this->getTranslation($vehicle->size);
    }
}
