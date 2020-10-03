<?php

declare(strict_types=1);

namespace App\Jobs\Api\StarCitizen\Vehicle\Parser\Element;

use App\Jobs\Api\StarCitizen\Vehicle\Parser\Element\AbstractBaseElement as BaseElement;
use App\Models\Api\StarCitizen\Manufacturer\Manufacturer as ManufacturerModel;

/**
 * Manufacturer Parser
 */
class Manufacturer extends BaseElement
{
    private const MANUFACTURER = 'manufacturer';
    private const MANUFACTURER_ID = 'manufacturer_id';
    private const MANUFACTURER_NAME = 'name';
    private const MANUFACTURER_CODE = 'code';
    private const MANUFACTURER_KNOWN_FOR = 'known_for';
    private const MANUFACTURER_DESCRIPTION = 'description';

    /**
     * @return ManufacturerModel
     */
    public function getManufacturer(): ManufacturerModel
    {
        app('Log')::debug('Getting Manufacturer');

        $manufacturerData = collect($this->rawData->get(self::MANUFACTURER));

        /** @var ManufacturerModel $manufacturer */
        $manufacturer = ManufacturerModel::query()->updateOrCreate(
            [
                'cig_id' => $this->rawData->get(self::MANUFACTURER_ID),
            ],
            [
                'name' => htmlspecialchars_decode($manufacturerData->get(self::MANUFACTURER_NAME)),
                'name_short' => $manufacturerData->get(self::MANUFACTURER_CODE),
            ]
        );

        $manufacturer->translations()->updateOrCreate(
            [
                'locale_code' => config('language.english'),
            ],
            [
                'known_for' => $manufacturerData->get(self::MANUFACTURER_KNOWN_FOR),
                'description' => $manufacturerData->get(self::MANUFACTURER_DESCRIPTION),
            ]
        );

        return $manufacturer;
    }
}
