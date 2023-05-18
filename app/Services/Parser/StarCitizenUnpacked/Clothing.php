<?php

declare(strict_types=1);

namespace App\Services\Parser\StarCitizenUnpacked;

use App\Services\Parser\StarCitizenUnpacked\AbstractCommodityItem;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use JsonException;

final class Clothing extends AbstractCommodityItem
{
    public function getData(): ?array
    {
        $attachDef = $this->getAttachDef();
        if ($attachDef === null) {
            return null;
        }

        $description = $this->getDescription($attachDef);

        $data = $this->tryExtractDataFromDescription($description, [
            'Manufacturer' => 'manufacturer',
            'Item Type' => 'type',
            'Damage Reduction' => 'damage_reduction',
            'Carrying Capacity' => 'carrying_capacity',
            'Temp. Rating' => 'temp_rating',
            'Core Compatibility' => 'core_compatibility',
        ]);

        if (str_contains($attachDef['Type'], 'Clothing')) {
            $name = $this->getName($attachDef, 'Unknown Clothing');
            $type = $this->getType($attachDef['Type'], $name);
        } else {
            $type = $data['type'] ?? 'Unknown Type';
        }

        return [
                'uuid' => $this->item->pull('Raw.Entity.__ref'),
                'description_key' => $this->getDescriptionKey($attachDef),
                'description' => $this->cleanString(trim($data['description'] ?? $description)),
                'type' => trim($type),
                'damage_reduction' => $data['damage_reduction'] ?? null,
                'carrying_capacity' => $data['carrying_capacity'] ?? null
            ] + $this->loadResistances();
    }


    private function loadResistances(): array
    {
        $tempResist = $this->get('SCItemClothingParams.TemperatureResistance', []);

        return [
            'resistances' => array_filter([
                'temp_min' => [
                    'threshold' => $tempResist['MinResistance'] ?? null,
                ],
                'temp_max' => [
                    'threshold' => $tempResist['MaxResistance'] ?? null,
                ],
                'physical' => array_filter([
                    'multiplier' => $this->item['damageResistances']['PhysicalResistance']['Multiplier'] ?? null,
                    'threshold' => $this->item['damageResistances']['PhysicalResistance']['Threshold'] ?? null,
                ]),
                'energy' => array_filter([
                    'multiplier' => $this->item['damageResistances']['EnergyResistance']['Multiplier'] ?? null,
                    'threshold' => $this->item['damageResistances']['EnergyResistance']['Threshold'] ?? null,

                ]),
                'distortion' => array_filter([
                    'multiplier' => $this->item['damageResistances']['DistortionResistance']['Multiplier'] ?? null,
                    'threshold' => $this->item['damageResistances']['DistortionResistance']['Threshold'] ?? null,

                ]),
                'thermal' => array_filter([
                    'multiplier' => $this->item['damageResistances']['ThermalResistance']['Multiplier'] ?? null,
                    'threshold' => $this->item['damageResistances']['ThermalResistance']['Threshold'] ?? null,

                ]),
                'biochemical' => array_filter([
                    'multiplier' => $this->item['damageResistances']['BiochemicalResistance']['Multiplier'] ?? null,
                    'threshold' => $this->item['damageResistances']['BiochemicalResistance']['Threshold'] ?? null,

                ]),
                'stun' => array_filter([
                    'multiplier' => $this->item['damageResistances']['StunResistance']['Multiplier'] ?? null,
                    'threshold' => $this->item['damageResistances']['StunResistance']['Threshold'] ?? null,
                ]),
            ]),
        ];
    }

    private function getType(string $type, string $name): string
    {
        switch (true) {
            case strpos($name, 'T-Shirt') !== false:
            case strpos($name, 'Shirt') !== false:
                return 'T-Shirt';

            case strpos($name, 'Jacket') !== false:
                return 'Jacket';

            case strpos($name, 'Gloves') !== false:
                return 'Gloves';

            case strpos($name, 'Pants') !== false:
                return 'Pants';

            case strpos($name, 'Bandana') !== false:
                return 'Bandana';

            case strpos($name, 'Beanie') !== false:
                return 'Beanie';

            case strpos($name, 'Boots') !== false:
                return 'Boots';

            case strpos($name, 'Sweater') !== false:
                return 'Sweater';

            case strpos($name, 'Hat') !== false:
                return 'Hat';

            case strpos($name, 'Shoes') !== false:
                return 'Shoes';

            case strpos($name, 'Head Cover') !== false:
                return 'Head Cover';

            case strpos($name, 'Gown') !== false:
                return 'Gown';

            case strpos($name, 'Slippers') !== false:
                return 'Slippers';
        }

        switch (true) {
            case strpos($type, 'Backpack') !== false:
                return 'Backpack';

            case strpos($type, 'Feet') !== false:
                return 'Shoes';

            case strpos($type, 'Hands') !== false:
                return 'Gloves';

            case strpos($type, 'Hat') !== false:
                return 'Hat';

            case strpos($type, 'Legs') !== false:
                return 'Pants';

            case strpos($type, 'Torso_0') !== false:
                return 'Shirt';

            case strpos($type, 'Torso_1') !== false:
                return 'Jacket';
        }

        return 'Unknown Type';
    }
}
