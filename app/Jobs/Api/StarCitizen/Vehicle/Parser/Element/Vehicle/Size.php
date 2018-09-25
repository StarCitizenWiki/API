<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 25.09.2018
 * Time: 12:52
 */

namespace App\Jobs\Api\StarCitizen\Vehicle\Parser\Element\Vehicle;

use App\Jobs\Api\StarCitizen\Vehicle\Parser\Element\AbstractBaseElement as BaseElement;
use App\Models\Api\StarCitizen\Vehicle\Size\Size as VehicleSize;
use App\Models\Api\StarCitizen\Vehicle\Size\SizeTranslation;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Class Size
 */
class Size extends BaseElement
{
    private const VEHICLE_SIZE = 'size';

    /**
     * @return \App\Models\Api\StarCitizen\Vehicle\Size\Size
     */
    public function getVehicleSize(): VehicleSize
    {
        app('Log')::debug('Getting Vehicle Size');

        $size = $this->rawData->get(self::VEHICLE_SIZE);

        if (null === $size) {
            app('Log')::debug('Vehicle Size not set in Matrix, returning default (undefined)');

            return VehicleSize::find(1);
        }

        try {
            /** @var \App\Models\Api\StarCitizen\Vehicle\Size\SizeTranslation $sizeTranslation */
            $sizeTranslation = SizeTranslation::query()->where(
                'translation',
                $size
            )->where(
                'locale_code',
                config('language.english')
            )->firstOrFail();
        } catch (ModelNotFoundException $e) {
            app('Log')::debug('Vehicle Size not found in DB');

            return $this->createNewVehicleSize();
        }

        return $sizeTranslation->vehicleSize;
    }

    /**
     * @return \App\Models\Api\StarCitizen\Vehicle\Size\Size
     */
    private function createNewVehicleSize(): VehicleSize
    {
        app('Log')::debug('Creating new Vehicle Size');

        /** @var \App\Models\Api\StarCitizen\Vehicle\Size\Size $size */
        $size = VehicleSize::create(
            [
                'slug' => str_slug($this->rawData->get(self::VEHICLE_SIZE)),
            ]
        );

        $size->translations()->create(
            [
                'locale_code' => config('language.english'),
                'translation' => $this->rawData->get(self::VEHICLE_SIZE),
            ]
        );

        app('Log')::debug('Vehicle Size created');

        return $size;
    }
}
