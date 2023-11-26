<?php

declare(strict_types=1);

namespace App\Services\Parser\SC\VehicleItems;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

final class SalvageModifier extends AbstractItemSpecification
{
    public static function getData(Collection $item): ?array
    {
        $data = self::get($item, 'EntityComponentAttachableModifierParams.modifiers.ItemWeaponModifiersParams.0.weaponModifier.weaponStats.salvageModifier');

        if (empty($data)) {
            return null;
        }

        return array_filter([
            'salvage_speed_multiplier' => Arr::get($data, 'salvageSpeedMultiplier'),
            'radius_multiplier' => Arr::get($data, 'radiusMultiplier'),
            'extraction_efficiency' => Arr::get($data, 'extractionEfficiency'),
        ], static function ($entry) {
            return $entry !== null;
        });
    }
}
