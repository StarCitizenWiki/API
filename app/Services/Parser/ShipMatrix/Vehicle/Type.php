<?php

declare(strict_types=1);

namespace App\Services\Parser\ShipMatrix\Vehicle;

use App\Models\StarCitizen\Vehicle\Type\Type as VehicleType;
use App\Models\StarCitizen\Vehicle\Type\TypeTranslation;
use App\Services\Parser\ShipMatrix\AbstractBaseElement as BaseElement;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Str;

/**
 * Class Type
 */
class Type extends BaseElement
{
    private const VEHICLE_TYPE = 'type';

    /**
     * @throws ModelNotFoundException
     */
    public function getVehicleType(): VehicleType
    {
        $type = $this->rawData->get(self::VEHICLE_TYPE);

        if ($type === null) {
            app('Log')::debug('Vehicle Type not set in Matrix, returning default (undefined)');

            return VehicleType::findOrFail(1);
        }

        try {
            /** @var TypeTranslation $typeTranslation */
            $typeTranslation = TypeTranslation::query()->where(
                'translation',
                $type
            )->where(
                'locale_code',
                config('language.english')
            )->firstOrFail();
        } catch (ModelNotFoundException $e) {
            return $this->createNewVehicleType();
        }

        return $typeTranslation->type;
    }

    private function createNewVehicleType(): VehicleType
    {
        $slug = Str::slug($this->rawData->get(self::VEHICLE_TYPE));
        $translation = $this->rawData->get(self::VEHICLE_TYPE);

        /** @var VehicleType $type */
        $type = VehicleType::query()->firstOrCreate(
            ['slug' => $slug],
            ['slug' => $slug]
        );

        $type->translations()->updateOrCreate(
            ['locale_code' => config('language.english')],
            ['translation' => $translation]
        );

        app('Log')::debug('Vehicle Type created', ['id' => $type->id]);

        return $type;
    }
}
