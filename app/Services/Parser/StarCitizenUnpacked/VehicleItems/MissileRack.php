<?php

declare(strict_types=1);

namespace App\Services\Parser\StarCitizenUnpacked\VehicleItems;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

final class MissileRack extends AbstractItemSpecification
{
    public static function getData(Collection $item): ?array
    {
        $data = self::getAttachDef($item);

        if ($data === null || ($data['Type'] !== 'MissileLauncher' && $data['SubType'] !== 'MissileRack')) {
            return null;
        }

        $ports = self::get($item, 'SItemPortContainerComponentParams');
        if ($ports === null || empty($ports['Ports'] ?? [])) {
            return null;
        }

        return array_filter([
            'missile_count' => count($ports['Ports']),
            'missile_size' => $ports['Ports'][0]['MaxSize'] ?? null,
        ]);
    }
}
