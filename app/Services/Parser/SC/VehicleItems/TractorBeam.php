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
            'min_force' => Arr::get($data, 'minForce'),
            'max_force' => Arr::get($data, 'maxForce'),
            'min_distance' => Arr::get($data, 'minDistance'),
            'max_distance' => Arr::get($data, 'maxDistance'),
            'full_strength_distance' => Arr::get($data, 'fullStrengthDistance'),
            'max_angle' => Arr::get($data, 'maxAngle'),
            'max_volume' => Arr::get($data, 'maxVolume'),
            'volume_force_coefficient' => Arr::get($data, 'volumeForceCoefficient'),
            'tether_break_time' => Arr::get($data, 'tetherBreakTime'),
            'safe_range_value_factor' => Arr::get($data, 'safeRangeValueFactor'),
        ], static function ($entry) {
            return $entry !== null;
        });
    }
}
