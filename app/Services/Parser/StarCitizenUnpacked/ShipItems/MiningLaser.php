<?php

declare(strict_types=1);

namespace App\Services\Parser\StarCitizenUnpacked\ShipItems;

use Illuminate\Support\Collection;

final class MiningLaser extends AbstractItemSpecification
{
    public static function getData(array $item, Collection $rawData): ?array
    {
        if (!isset($rawData['Components']['SEntityComponentMiningLaserParams'])) {
            return null;
        }

        // phpcs:disable
        return array_filter([
            'hit_type' => $rawData['Components']['SCItemWeaponComponentParams']['fireActions'][0]['hitType'],
            'energy_rate' => $rawData['Components']['SCItemWeaponComponentParams']['fireActions'][0]['energyRate'] ?? 0,
            'full_damage_range' => $rawData['Components']['SCItemWeaponComponentParams']['fireActions'][0]['fullDamageRange'] ?? 0,
            'zero_damage_range' => $rawData['Components']['SCItemWeaponComponentParams']['fireActions'][0]['zeroDamageRange'] ?? 0,
            'heat_per_second' => $rawData['Components']['SCItemWeaponComponentParams']['fireActions'][0]['heatPerSecond'] ?? 0,
            'damage' => $rawData['Components']['SCItemWeaponComponentParams']['fireActions'][0]['damagePerSecond']['DamageInfo']['DamageEnergy'] ?? 0,

            'modifier_resistance' => $rawData['Components']['SEntityComponentMiningLaserParams']['miningLaserModifiers']['resistanceModifier'] ?? 0,
            'modifier_instability' => $rawData['Components']['SEntityComponentMiningLaserParams']['miningLaserModifiers']['laserInstability']['FloatModifierMultiplicative']['value'] ?? 0,
            'modifier_charge_window_size' => $rawData['Components']['SEntityComponentMiningLaserParams']['miningLaserModifiers']['optimalChargeWindowSizeModifier']['FloatModifierMultiplicative']['value'] ?? 0,
            'modifier_charge_window_rate' => $rawData['Components']['SEntityComponentMiningLaserParams']['miningLaserModifiers']['optimalChargeWindowRateModifier']['FloatModifierMultiplicative']['value'] ?? 0,
            'modifier_shatter_damage' => $rawData['Components']['SEntityComponentMiningLaserParams']['miningLaserModifiers']['shatterdamageModifier']['FloatModifierMultiplicative']['value'] ?? 0,
            'modifier_catastrophic_window_rate' => $rawData['Components']['SEntityComponentMiningLaserParams']['miningLaserModifiers']['catastrophicChargeWindowRateModifier']['FloatModifierMultiplicative']['value'] ?? 0,
        ], static function ($entry) {
            return !empty($entry);
        });
        // phpcs:enable
    }
}
