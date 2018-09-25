<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 25.09.2018
 * Time: 12:54
 */

namespace App\Jobs\Api\StarCitizen\Vehicle\Parser\Element\Vehicle;

use App\Jobs\Api\StarCitizen\Vehicle\Parser\Element\AbstractBaseElement as BaseElement;
use App\Models\Api\StarCitizen\Vehicle\Type\Type as VehicleType;
use App\Models\Api\StarCitizen\Vehicle\Type\TypeTranslation;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Class Type
 */
class Type extends BaseElement
{
    private const VEHICLE_TYPE = 'type';

    /**
     * @return \App\Models\Api\StarCitizen\Vehicle\Type\Type
     */
    public function getVehicleType(): VehicleType
    {
        app('Log')::debug('Getting Vehicle Type');

        $type = $this->rawData->get(self::VEHICLE_TYPE);

        if (null === $type) {
            app('Log')::debug('Vehicle Type not set in Matrix, returning default (undefined)');

            return VehicleType::find(1);
        }

        try {
            /** @var \App\Models\Api\StarCitizen\Vehicle\Type\TypeTranslation $typeTranslation */
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

        return $typeTranslation->vehicleType;
    }

    /**
     * @return \App\Models\Api\StarCitizen\Vehicle\Type\Type
     */
    private function createNewVehicleType(): VehicleType
    {
        app('Log')::debug('Creating new Vehicle Type');

        /** @var \App\Models\Api\StarCitizen\Vehicle\Type\Type $type */
        $type = VehicleType::create(
            [
                'slug' => str_slug($this->rawData->get(self::VEHICLE_TYPE)),
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
