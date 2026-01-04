<?php

declare(strict_types=1);

namespace App\Services\Parser\ShipMatrix\Vehicle;

use App\Models\StarCitizen\ShipMatrix\Vehicle\Size as VehicleSize;
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
            return VehicleSize::query()
                ->where('translation->'.config('language.english'), $size)
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            app('Log')::debug('Vehicle Size not found in DB');

            return $this->createNewVehicleSize();
        }
    }

    private function createNewVehicleSize(): VehicleSize
    {
        $slug = Str::slug($this->rawData->get(self::VEHICLE_SIZE));
        $translation = $this->rawData->get(self::VEHICLE_SIZE);

        /** @var VehicleSize $size */
        $size = VehicleSize::query()->firstOrCreate(
            ['slug' => $slug],
            ['slug' => $slug]
        );

        if ($translation !== null && $translation !== '') {
            $size->setTranslation('translation', config('language.english'), $translation);
            $size->save();
        }

        app('Log')::debug('Vehicle Size created', ['id' => $size->id]);

        return $size;
    }
}
