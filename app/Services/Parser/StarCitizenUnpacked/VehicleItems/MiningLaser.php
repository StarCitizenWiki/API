<?php

declare(strict_types=1);

namespace App\Services\Parser\StarCitizenUnpacked\VehicleItems;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

final class MiningLaser extends AbstractItemSpecification
{
    public static function getData(Collection $item): ?array
    {
        $data = Arr::get($item, 'Raw.Entity.Components.SEntityComponentMiningLaserParams');

        if ($data === null) {
            return null;
        }

        $mappings = [
            'resistanceModifier' => 'modifier_resistance',
            'laserInstability' => 'modifier_instability',
            'optimalChargeWindowSizeModifier' => 'modifier_charge_window_size',
            'shatterdamageModifier' => 'modifier_charge_window_rate',
            'optimalChargeWindowRateModifier' => 'modifier_shatter_damage',
            'catastrophicChargeWindowRateModifier' => 'modifier_catastrophic_window_rate',
        ];

        $out = [];

        foreach ($data['miningLaserModifiers'] as $modifier => $datum) {
            if (is_array($datum)) {
                $data = array_pop($datum);
                $data = array_pop($data);
            } else {
                $data = $datum;
            }

            $out[$mappings[$modifier]] = $data;
        }

        return array_filter($out, static function ($entry) {
            return $entry !== null;
        });
    }
}
