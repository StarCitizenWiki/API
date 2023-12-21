<?php

declare(strict_types=1);

namespace App\Services\Parser\SC\VehicleItems;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

final class CargoGrid extends AbstractItemSpecification
{
    public static function getData(Collection $item): ?array
    {
        $data = self::get($item, 'SCItemCargoGridParams');
        $attachDef = self::getAttachDef($item);

        if ($data === null) {
            if ($attachDef['Type'] === 'CargoGrid' && isset($item['inventoryContainer'])) {
                $data = collect([
                    'dimensions' => [
                        'x' => $item['inventoryContainer']['x'],
                        'y' => $item['inventoryContainer']['y'],
                        'z' => $item['inventoryContainer']['z'],
                    ]
                ]);
            } else {
                return null;
            }
        }

        return array_filter([
            'x' => Arr::get($data, 'dimensions.x'),
            'y' => Arr::get($data, 'dimensions.y'),
            'z' => Arr::get($data, 'dimensions.z'),
        ], static function ($entry) {
            return !empty($entry);
        });
    }
}
