<?php

declare(strict_types=1);

namespace App\Services\Parser\StarCitizenUnpacked\VehicleItems;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

final class Emp extends AbstractItemSpecification
{
    public static function getData(Collection $item): ?array
    {
        $data = self::get($item, 'SCItemEMPParams');

        if ($data === null) {
            return null;
        }

        return array_filter([
            'charge_duration' => Arr::get($data, 'chargeTime'),
            'emp_radius' => Arr::get($data, 'empRadius'),
            'unleash_duration' => Arr::get($data, 'unleashTime'),
            'cooldown_duration' => Arr::get($data, 'cooldownTime'),
        ], static function ($entry) {
            return $entry !== null;
        });
    }
}
