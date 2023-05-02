<?php

declare(strict_types=1);

namespace App\Services\Parser\StarCitizenUnpacked\ShipItems;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

final class CounterMeasure extends AbstractItemSpecification
{
    public static function getData(Collection $item): ?array
    {
        $data = self::get($item, 'SAmmoContainerComponentParams');

        if ($data === null) {
            return null;
        }

        return array_filter([
            'initial_ammo_count' => Arr::get($data, 'initialAmmoCount'),
            'max_ammo_count' => Arr::get($data, 'maxAmmoCount'),
        ], static function ($entry) {
            return $entry !== null;
        });
    }
}
