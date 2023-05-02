<?php

declare(strict_types=1);

namespace App\Services\Parser\StarCitizenUnpacked\ShipItems;

use Illuminate\Support\Collection;

final class PowerPlant extends AbstractItemSpecification
{
    public static function getData(Collection $item): ?array
    {
        $attachDef = self::getAttachDef($item);

        if ($attachDef === null || (!isset($attachDef['Type']) && $attachDef['Type'] !== 'PowerPlant')) {
            return null;
        }

        $powerPlant = self::get($item, 'EntityComponentPowerConnection');

        if ($powerPlant === null) {
            return null;
        }

        return [
            'power_output' => $powerPlant['PowerDraw'],
        ];
    }
}
