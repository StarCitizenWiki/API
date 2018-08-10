<?php declare(strict_types = 1);
/**
 * Created by PhpStorm.
 * User: Hanne
 * Date: 09.08.2018
 * Time: 16:13
 */

namespace App\Transformers\Api\V1\StarCitizen\Manufacturer;

use App\Models\Api\StarCitizen\Manufacturer\Manufacturer;
use App\Transformers\Api\LocaleAwareTransformerInterface;
use League\Fractal\TransformerAbstract;

/**
 * Manufacturer Transformer
 */
class ManufacturerTransformer extends TransformerAbstract implements LocaleAwareTransformerInterface
{
    /**
     * @param \App\Models\Api\StarCitizen\Manufacturer\Manufacturer $manufacturer
     *
     * @return array
     */
    public function transform(Manufacturer $manufacturer)
    {
        $manufacturerTransformed = [
            'code' => $manufacturer->name_short,
            'name' => $manufacturer->name,
            'known_for' => '',
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
     * @param string $localeCode
     *
     * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     *
     * @return void
     */
    public function setLocale(string $localeCode)
    {
        // TODO: Implement setLocale() method.
    }
}
