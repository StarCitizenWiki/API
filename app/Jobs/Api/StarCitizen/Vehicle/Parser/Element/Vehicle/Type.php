<?php declare(strict_types = 1);

namespace App\Jobs\Api\StarCitizen\Vehicle\Parser\Element\Vehicle;

use App\Jobs\Api\StarCitizen\Vehicle\Parser\Element\AbstractBaseElement as BaseElement;
use App\Models\Api\StarCitizen\Vehicle\Type\Type as VehicleType;
use App\Models\Api\StarCitizen\Vehicle\Type\TypeTranslation;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Str;

/**
 * Class Type
 */
class Type extends BaseElement
{
    private const VEHICLE_TYPE = 'type';

    /**
     * @return VehicleType
     *
     * @throws ModelNotFoundException
     */
    public function getVehicleType(): VehicleType
    {
        app('Log')::debug('Getting Vehicle Type');

        $type = $this->rawData->get(self::VEHICLE_TYPE);

        if (null === $type) {
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
            app('Log')::debug('Vehicle Type not found in DB');

            return $this->createNewVehicleType();
        }

        return $typeTranslation->type;
    }

    /**
     * @return VehicleType
     */
    private function createNewVehicleType(): VehicleType
    {
        app('Log')::debug('Creating new Vehicle Type');

        /** @var VehicleType $type */
        $type = VehicleType::create(
            [
                'slug' => Str::slug($this->rawData->get(self::VEHICLE_TYPE)),
            ]
        );

        $type->translations()->create(
            [
                'locale_code' => config('language.english'),
                'translation' => $this->rawData->get(self::VEHICLE_TYPE),
            ]
        );

        app('Log')::debug('Vehicle Type created');

        return $type;
    }
}
