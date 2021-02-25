<?php

declare(strict_types=1);

namespace App\Services\Parser\ShipMatrix\Vehicle;

use App\Services\Parser\ShipMatrix\AbstractBaseElement as BaseElement;
use App\Models\StarCitizen\Vehicle\Size\Size as VehicleSize;
use App\Models\StarCitizen\Vehicle\Size\SizeTranslation;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Str;

/**
 * Class Size
 */
class Size extends BaseElement
{
    private const VEHICLE_SIZE = 'size';

    /**
     * @return VehicleSize
     *
     * @throws ModelNotFoundException
     */
    public function getVehicleSize(): VehicleSize
    {
        app('Log')::debug('Getting Vehicle Size');

        $size = $this->rawData->get(self::VEHICLE_SIZE);

        if (null === $size) {
            app('Log')::debug('Vehicle Size not set in Matrix, returning default (undefined)');

            return VehicleSize::findOrFail(1);
        }

        try {
            /** @var SizeTranslation $sizeTranslation */
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

        return $sizeTranslation->size;
    }

    /**
     * @return VehicleSize
     */
    private function createNewVehicleSize(): VehicleSize
    {
        app('Log')::debug('Creating new Vehicle Size');

        /** @var VehicleSize $size */
        $size = VehicleSize::create(
            [
                'slug' => Str::slug($this->rawData->get(self::VEHICLE_SIZE)),
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
