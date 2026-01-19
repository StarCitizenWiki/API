<?php

declare(strict_types=1);

namespace App\Services\Parser\ShipMatrix;

use App\Models\StarCitizen\ShipMatrix\Manufacturer as ManufacturerModel;
use App\Services\Parser\ShipMatrix\AbstractBaseElement as BaseElement;

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

    public function getManufacturer(): ManufacturerModel
    {
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

        $locale = config('language.english');
        $knownFor = $manufacturerData->get(self::MANUFACTURER_KNOWN_FOR);
        $description = $manufacturerData->get(self::MANUFACTURER_DESCRIPTION);

        if ($knownFor !== null && $knownFor !== '') {
            $manufacturer->setTranslation('known_for', $locale, $knownFor);
        }

        if ($description !== null && $description !== '') {
            $manufacturer->setTranslation('description', $locale, $description);
        }

        $manufacturer->save();

        return $manufacturer;
    }
}
