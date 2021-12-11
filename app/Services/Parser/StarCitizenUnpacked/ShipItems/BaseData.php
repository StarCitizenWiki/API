<?php

declare(strict_types=1);

namespace App\Services\Parser\StarCitizenUnpacked\ShipItems;

use Illuminate\Support\Collection;

final class BaseData extends AbstractItemSpecification
{
    public static function getData(array $item, Collection $rawData): ?array
    {
        $out = [
            'health' => $rawData->pull('Components.SHealthComponentParams.Health', $item['Durability']['Health'] ?? 0),
            'lifetime' => $rawData->pull('Components.SDegradationParams.MaxLifetimeHours', $item['Durability']['Lifetime'] ?? 0),
        ];

        $out['power'] = self::addPowerData($rawData);
        $out['heat'] = self::addHeatData($rawData);
        $out['distortion'] = self::addDistortionData($rawData);
        $out['durability'] = self::addDurability($rawData);

        return array_filter($out);
    }

    private static function addPowerData(Collection $rawData): array
    {
        if (!isset($rawData['Components']['EntityComponentPowerConnection'])) {
            return [];
        }

        $basePath = 'Components.EntityComponentPowerConnection.';

        return array_filter([
            'power_base' => $rawData->pull($basePath . 'PowerBase'),
            'power_draw' => $rawData->pull($basePath . 'PowerDraw'),

            'throttleable' => $rawData->pull($basePath . 'IsThrottleable'),
            'overclockable' => $rawData->pull($basePath . 'IsOverclockable'),

            'overclock_threshold_min' => $rawData->pull($basePath . 'OverclockThresholdMin'),
            'overclock_threshold_max' => $rawData->pull($basePath . 'OverclockThresholdMax'),
            'overclock_performance' => $rawData->pull($basePath . 'OverclockPerformance'),

            'overpower_performance' => $rawData->pull($basePath . 'OverpowerPerformance'),

            'power_to_em' => $rawData->pull($basePath . 'PowerToEM'),
            'decay_rate_em' => $rawData->pull($basePath . 'DecayRateOfEM'),
        ], static function ($entry) {
            return $entry !== null;
        });
    }

    private static function addHeatData(Collection $rawData): array
    {
        if (!isset($rawData['Components']['EntityComponentHeatConnection'])) {
            return [];
        }

        $basePath = 'Components.EntityComponentHeatConnection.';

        return array_filter([
            'temperature_to_ir' => $rawData->pull($basePath . 'TemperatureToIR'),
            'overpower_heat' => $rawData->pull($basePath . 'OverpowerHeat'),
            'overclock_threshold_min' => $rawData->pull($basePath . 'OverclockThresholdMinHeat'),
            'overclock_threshold_max' => $rawData->pull($basePath . 'OverclockThresholdMaxHeat'),
            'thermal_energy_base' => $rawData->pull($basePath . 'ThermalEnergyBase'),
            'thermal_energy_draw' => $rawData->pull($basePath . 'ThermalEnergyDraw'),
            'thermal_conductivity' => $rawData->pull($basePath . 'ThermalConductivity'),
            'specific_heat_capacity' => $rawData->pull($basePath . 'SpecificHeatCapacity'),
            'mass' => $rawData->pull($basePath . 'Mass'),
            'surface_area' => $rawData->pull($basePath . 'SurfaceArea'),
            'start_cooling_temperature' => $rawData->pull($basePath . 'StartCoolingTemperature'),
            'max_cooling_rate' => $rawData->pull($basePath . 'MaxCoolingRate'),
            'max_temperature' => $rawData->pull($basePath . 'MaxTemperature'),
            'min_temperature' => $rawData->pull($basePath . 'MinTemperature'),
            'overheat_temperature' => $rawData->pull($basePath . 'OverheatTemperature'),
            'recovery_temperature' => $rawData->pull($basePath . 'RecoveryTemperature'),
            'misfire_min_temperature' => $rawData->pull($basePath . 'MisfireMinTemperature'),
            'misfire_max_temperature' => $rawData->pull($basePath . 'MisfireMaxTemperature'),
        ], static function ($entry) {
            return $entry !== null;
        });
    }

    private static function addDistortionData(Collection $rawData): array
    {
        if (!isset($rawData['Components']['SDistortionParams'])) {
            return [];
        }

        $basePath = 'Components.SDistortionParams.';

        return array_filter([
            'decay_rate' => $rawData->pull($basePath . 'DecayRate'),

            'maximum' => $rawData->pull($basePath . 'Maximum'),

            'overload_ratio' => $rawData->pull($basePath . 'OverloadRatio'),

            'recovery_ratio' => $rawData->pull($basePath . 'RecoveryRatio'),
            'recovery_time' => $rawData->pull($basePath . 'RecoveryTime'),
        ], static function ($entry) {
            return $entry !== null;
        });
    }

    private static function addDurability(Collection $rawData): array
    {
        if (!isset($rawData['Components']['SHealthComponentParams']) && !isset($rawData['Components']['SDegradationParams'])) {
            return [];
        }

        return array_filter([
            'health' => $rawData->pull('Components.SHealthComponentParams.Health'),
            'max_lifetime' => $rawData->pull('Components.SDegradationParams.MaxLifetimeHours', 0),
        ], static function ($entry) {
            return $entry !== null;
        });
    }
}
