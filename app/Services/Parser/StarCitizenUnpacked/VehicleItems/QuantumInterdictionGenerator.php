<?php

declare(strict_types=1);

namespace App\Services\Parser\StarCitizenUnpacked\VehicleItems;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

final class QuantumInterdictionGenerator extends AbstractItemSpecification
{
    public static function getData(Collection $item): ?array
    {
        $data = self::get($item, 'SCItemQuantumInterdictionGeneratorParams');

        if ($data === null) {
            return null;
        }

        return array_filter([
            'jammer_range' => Arr::get($data, 'jammerSettings.0.jammerRange'),
            'interdiction_range' => Arr::get($data, 'quantumInterdictionPulseSettings.0.radiusMeters'),
            'charge_duration' => Arr::get($data, 'quantumInterdictionPulseSettings.0.chargeTimeSecs'),
            'discharge_duration' => Arr::get($data, 'quantumInterdictionPulseSettings.0.dischargeTimeSecs'),
            'cooldown_duration' => Arr::get($data, 'quantumInterdictionPulseSettings.0.cooldownTimeSecs'),
        ], static function ($entry) {
            return $entry !== null;
        });
    }
}
