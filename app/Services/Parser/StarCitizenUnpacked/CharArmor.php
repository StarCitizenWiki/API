<?php

declare(strict_types=1);

namespace App\Services\Parser\StarCitizenUnpacked;

use App\Services\Parser\StarCitizenUnpacked\AbstractCommodityItem;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use JsonException;

final class CharArmor extends AbstractCommodityItem
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
            'Temp\. Rating' => 'temp_rating',
            'Core Compatibility' => 'core_compatibility',
        ]);

        return [
                'uuid' => $this->item->pull('Raw.Entity.__ref'),
                'description' => $this->cleanString(trim($data['description'] ?? $description)),
                'type' => trim($data['type'] ?? 'Unknown Type'),
                'damage_reduction' => $data['damage_reduction'] ?? null,
                'carrying_capacity' => $data['carrying_capacity'] ?? null
            ] + $this->loadResistances();
    }


    private function loadResistances(): array
    {
        $tempResist = $this->get('SCItemClothingParams.TemperatureResistance', []);

        return [
            'temp_resistance_min' => $tempResist['MinResistance'] ?? null,
            'temp_resistance_max' => $tempResist['MaxResistance'] ?? null,
            'resistances' => array_filter([
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
}
