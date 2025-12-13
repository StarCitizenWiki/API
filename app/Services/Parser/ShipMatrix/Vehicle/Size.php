<?php

declare(strict_types=1);

namespace App\Services\Parser\ShipMatrix\Vehicle;

use App\Models\StarCitizen\Vehicle\Size\Size as VehicleSize;
use App\Models\StarCitizen\Vehicle\Size\SizeTranslation;
use App\Services\Parser\ShipMatrix\AbstractBaseElement as BaseElement;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Str;

/**
 * Class Size
 */
class Size extends BaseElement
{
    private const VEHICLE_SIZE = 'size';

    /**
     * @throws ModelNotFoundException
     */
    public function getVehicleSize(): VehicleSize
    {
        app('Log')::debug('Getting Vehicle Size');

        $size = $this->rawData->get(self::VEHICLE_SIZE);

        if ($size === null) {
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

    private function createNewVehicleSize(): VehicleSize
    {
        app('Log')::debug('Creating new Vehicle Size');

        $slug = Str::slug($this->rawData->get(self::VEHICLE_SIZE));
        $translation = $this->rawData->get(self::VEHICLE_SIZE);

        // Race-safe: slug has unique constraint
        /** @var VehicleSize $size */
        $size = VehicleSize::query()->firstOrCreate(
            ['slug' => $slug],
            ['slug' => $slug]
        );

        // Race-safe translation update
        $size->translations()->updateOrCreate(
            ['locale_code' => config('language.english')],
            ['translation' => $translation]
        );

        app('Log')::debug('Vehicle Size created', ['id' => $size->id]);

        return $size;
    }
}
