<?php

declare(strict_types=1);

namespace App\Services\Parser\StarCitizenUnpacked\ShipItems;

use Illuminate\Support\Collection;

final class Shield extends AbstractItemSpecification
{
    public static function getData(array $item, Collection $rawData): ?array
    {
        if (!isset($rawData['Components']['SCItemShieldGeneratorParams'])) {
            return null;
        }

        $basePath = 'Components.SCItemShieldGeneratorParams.';

        return array_filter([
            'max_shield_health' => $rawData->pull($basePath . 'MaxShieldHealth'),
            'max_shield_regen' => $rawData->pull($basePath . 'MaxShieldRegen'),
            'decay_ratio' => $rawData->pull($basePath . 'DecayRatio'),
            'downed_regen_delay' => $rawData->pull($basePath . 'DownedRegenDelay'),
            'damage_regen_delay' => $rawData->pull($basePath . 'DamagedRegenDelay'),
            'max_reallocation' => $rawData->pull($basePath . 'MaxReallocation'),
            'reallocation_rate' => $rawData->pull($basePath . 'ReallocationRate'),
            'shield_hardening_factor' => $rawData->pull($basePath . 'ShieldHardening.Factor'),
            'shield_hardening_duration' => $rawData->pull($basePath . 'ShieldHardening.Duration'),
            'shield_hardening_cooldown' => $rawData->pull($basePath . 'ShieldHardening.Cooldown'),

            'absorptions' => [
                'physical' => array_filter([
                    'min' => $item['Shield']['Absorption']['Physical']['Minimum'],
                    'max' => $item['Shield']['Absorption']['Physical']['Maximum'],
                ]),
                'energy' => array_filter([
                    'min' => $item['Shield']['Absorption']['Energy']['Minimum'],
                    'max' => $item['Shield']['Absorption']['Energy']['Maximum'],
                ]),
                'distortion' => array_filter([
                    'min' => $item['Shield']['Absorption']['Distortion']['Minimum'],
                    'max' => $item['Shield']['Absorption']['Distortion']['Maximum'],
                ]),
                'thermal' => array_filter([
                    'min' => $item['Shield']['Absorption']['Thermal']['Minimum'],
                    'max' => $item['Shield']['Absorption']['Thermal']['Maximum'],
                ]),
                'biochemical' => array_filter([
                    'min' => $item['Shield']['Absorption']['Biochemical']['Minimum'],
                    'max' => $item['Shield']['Absorption']['Biochemical']['Maximum'],
                ]),
                'stun' => array_filter([
                    'min' => $item['Shield']['Absorption']['Stun']['Minimum'],
                    'max' => $item['Shield']['Absorption']['Stun']['Maximum'],
                ]),
            ],
        ], static function ($entry) {
            return $entry !== null;
        });
    }
}
