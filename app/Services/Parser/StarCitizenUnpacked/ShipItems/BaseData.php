<?php

declare(strict_types=1);

namespace App\Services\Parser\StarCitizenUnpacked\ShipItems;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

final class BaseData extends AbstractItemSpecification
{
    public static function getData(Collection $item): ?array
    {
        $out = [
            'health' => Arr::get($item, 'Raw.Entity.Components.SHealthComponentParams.Health'),
            'lifetime' => Arr::get($item, 'Raw.Entity.Components.SDegradationParams.MaxLifetimeHours'),
        ];

        $item = collect(Arr::get($item, 'Raw.Entity', []));

        $out['power'] = self::addPowerData($item);
        $out['heat'] = self::addHeatData($item);
        $out['distortion'] = self::addDistortionData($item);
        $out['durability'] = self::addDurability($item);

        return $out;
    }

    private static function addPowerData(Collection $item): array
    {
        if (!isset($item['Components']['EntityComponentPowerConnection'])) {
            return [];
        }

        $basePath = 'Components.EntityComponentPowerConnection.';

        return array_filter([
            'power_base' => Arr::get($item, $basePath . 'PowerBase'),
            'power_draw' => Arr::get($item, $basePath . 'PowerDraw'),

            'throttleable' => Arr::get($item, $basePath . 'IsThrottleable'),
            'overclockable' => Arr::get($item, $basePath . 'IsOverclockable'),

            'overclock_threshold_min' => Arr::get($item, $basePath . 'OverclockThresholdMin'),
            'overclock_threshold_max' => Arr::get($item, $basePath . 'OverclockThresholdMax'),
            'overclock_performance' => Arr::get($item, $basePath . 'OverclockPerformance'),

            'overpower_performance' => Arr::get($item, $basePath . 'OverpowerPerformance'),

            'power_to_em' => Arr::get($item, $basePath . 'PowerToEM'),
            'decay_rate_em' => Arr::get($item, $basePath . 'DecayRateOfEM'),
        ], static function ($entry) {
            return $entry !== null;
        });
    }

    private static function addHeatData(Collection $item): array
    {
        if (!isset($item['Components']['EntityComponentHeatConnection'])) {
            return [];
        }

        $basePath = 'Components.EntityComponentHeatConnection.';

        return array_filter([
            'temperature_to_ir' => Arr::get($item, $basePath . 'TemperatureToIR'),
            'overpower_heat' => Arr::get($item, $basePath . 'OverpowerHeat'),
            'overclock_threshold_min' => Arr::get($item, $basePath . 'OverclockThresholdMinHeat'),
            'overclock_threshold_max' => Arr::get($item, $basePath . 'OverclockThresholdMaxHeat'),
            'thermal_energy_base' => Arr::get($item, $basePath . 'ThermalEnergyBase'),
            'thermal_energy_draw' => Arr::get($item, $basePath . 'ThermalEnergyDraw'),
            'thermal_conductivity' => Arr::get($item, $basePath . 'ThermalConductivity'),
            'specific_heat_capacity' => Arr::get($item, $basePath . 'SpecificHeatCapacity'),
            'mass' => Arr::get($item, $basePath . 'Mass'),
            'surface_area' => Arr::get($item, $basePath . 'SurfaceArea'),
            'start_cooling_temperature' => Arr::get($item, $basePath . 'StartCoolingTemperature'),
            'max_cooling_rate' => Arr::get($item, $basePath . 'MaxCoolingRate'),
            'max_temperature' => Arr::get($item, $basePath . 'MaxTemperature'),
            'min_temperature' => Arr::get($item, $basePath . 'MinTemperature'),
            'overheat_temperature' => Arr::get($item, $basePath . 'OverheatTemperature'),
            'recovery_temperature' => Arr::get($item, $basePath . 'RecoveryTemperature'),
            'misfire_min_temperature' => Arr::get($item, $basePath . 'MisfireMinTemperature'),
            'misfire_max_temperature' => Arr::get($item, $basePath . 'MisfireMaxTemperature'),
        ], static function ($entry) {
            return $entry !== null;
        });
    }

    private static function addDistortionData(Collection $item): array
    {
        if (!isset($item['Components']['SDistortionParams'])) {
            return [];
        }

        $basePath = 'Components.SDistortionParams.';

        return array_filter([
            'decay_rate' => Arr::get($item, $basePath . 'DecayRate'),

            'maximum' => Arr::get($item, $basePath . 'Maximum'),

            'overload_ratio' => Arr::get($item, $basePath . 'OverloadRatio'),

            'recovery_ratio' => Arr::get($item, $basePath . 'RecoveryRatio'),
            'recovery_time' => Arr::get($item, $basePath . 'RecoveryTime'),
        ], static function ($entry) {
            return $entry !== null;
        });
    }

    private static function addDurability(Collection $item): array
    {
        if (!isset($item['Components']['SHealthComponentParams']) && !isset($item['Components']['SDegradationParams'])) {
            return [];
        }

        return array_filter([
            'health' => Arr::get($item, 'Components.SHealthComponentParams.Health'),
            'max_lifetime' => Arr::get($item, 'Components.SDegradationParams.MaxLifetimeHours', 0),
        ], static function ($entry) {
            return $entry !== null;
        });
    }
}
