<?php

declare(strict_types=1);

namespace App\Services\Parser\SC\VehicleItems;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

final class TractorBeam extends AbstractItemSpecification
{
    public static function getData(Collection $item): ?array
    {
        $data = self::get($item, 'SCItemWeaponComponentParams.fireActions');

        if (empty($data)) {
            return null;
        }

        $beam = collect($data)->first(fn($entry) => $entry['name'] === 'TractorBeam');

        if (empty($beam)) {
            return null;
        }

        return array_filter([
            'min_force' => Arr::get($beam, 'minForce'),
            'max_force' => Arr::get($beam, 'maxForce'),
            'min_distance' => Arr::get($beam, 'minDistance'),
            'max_distance' => Arr::get($beam, 'maxDistance'),
            'full_strength_distance' => Arr::get($beam, 'fullStrengthDistance'),
            'max_angle' => Arr::get($beam, 'maxAngle'),
            'max_volume' => Arr::get($beam, 'maxVolume'),
            'volume_force_coefficient' => Arr::get($beam, 'volumeForceCoefficient'),
            'tether_break_time' => Arr::get($beam, 'tetherBreakTime'),
            'safe_range_value_factor' => Arr::get($beam, 'safeRangeValueFactor'),
        ], static function ($entry) {
            return $entry !== null;
        });
    }
}
