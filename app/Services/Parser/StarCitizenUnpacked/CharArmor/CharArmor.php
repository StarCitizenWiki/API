<?php

declare(strict_types=1);

namespace App\Services\Parser\StarCitizenUnpacked\CharArmor;

use App\Services\Parser\StarCitizenUnpacked\AbstractCommodityItem;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use JsonException;

final class CharArmor extends AbstractCommodityItem
{
    private Collection $items;

    /**
     * AssaultRifle constructor.
     * @throws FileNotFoundException
     * @throws JsonException
     */
    public function __construct()
    {
        $items = File::get(storage_path(sprintf('app/api/scunpacked/api/dist/json/fps-items.json')));
        $this->items = collect(json_decode($items, true, 512, JSON_THROW_ON_ERROR));
    }

    public function getData(): Collection
    {
        return $this->items->filter(function (array $entry) {
            return strpos($entry['type'] ?? '', 'Char_Armor') !== false;
        })
            ->filter(function (array $entry) {
                return isset($entry['reference']);
            })
            ->map(function (array $entry) {
                $out = $entry['stdItem'] ?? [];
                $out['reference'] = $entry['reference'] ?? null;

                return $out;
            })
            ->filter(function (array $entry) {
                return isset($entry['Description']) && !empty($entry);
            })
            ->map(function (array $entry) {
                $data = [];
                try {
                    $data = File::get(storage_path(
                        sprintf('app/api/scunpacked/api/dist/json/items/%s.json', $entry['ClassName'])
                    ));
                    $data = json_decode($data, true, 512, JSON_THROW_ON_ERROR);
                } catch (FileNotFoundException | JsonException $e) {
                    //
                }

                return $this->map($entry, $data);
            })
            ->unique('name');
    }

    private function map(array $armor, array $itemData): array
    {
        $data = $this->tryExtractDataFromDescription($armor['Description'], [
            'Manufacturer' => 'manufacturer',
            'Item Type' => 'type',
            'Damage Reduction' => 'damage_reduction',
            'Carrying Capacity' => 'carrying_capacity',
            'Temp. Rating' => 'temp_rating',
        ]);

        return [
                'uuid' => $armor['reference'],
                'size' => $armor['Size'] ?? 0,
                'description' => str_replace(['’', '`', '´'], '\'', trim($data['description'] ?? '')),
                'name' => str_replace(
                    [
                        '“',
                        '”',
                        '"',
                        '\'',
                    ],
                    '"',
                    trim($armor['Name'] ?? 'Unknown Armor')
                ),
                'manufacturer' => $this->getManufacturer($armor),
                'type' => trim($data['type'] ?? 'Unknown Type'),
                'class' => trim($armor['Classification'] ?? 'Unknown Class'),
                'attachments' => $this->buildAttachmentsPart($itemData),
                'carrying_capacity' => $data['carrying_capacity'] ?? null
            ] + $this->loadResistances($itemData);
    }

    private function buildAttachmentsPart(array $data): array
    {
        if (!isset($data['Raw']['Entity']['Components']['SItemPortContainerComponentParams']['Ports'])) {
            return [];
        }

        $out = [];

        foreach ($data['Raw']['Entity']['Components']['SItemPortContainerComponentParams']['Ports'] as $port) {
            $out[$port['DisplayName']] = [
                'name' => $port['DisplayName'],
                'min_size' => $port['MinSize'] ?? 0,
                'max_size' => $port['MaxSize'] ?? 0,
            ];
        }

        return $out;
    }

    private function loadResistances(array $data): array
    {
        // phpcs:disable
        return [
            'temp_resistance_min' => $data['Raw']['Entity']['Components']['SCItemClothingParams']['TemperatureResistance']['MinResistance'] ?? 0,
            'temp_resistance_max' => $data['Raw']['Entity']['Components']['SCItemClothingParams']['TemperatureResistance']['MaxResistance'] ?? 0,
            'resistance_physical_multiplier' => $data['damageResistances']['PhysicalResistance']['Multiplier'] ?? 0,
            'resistance_physical_threshold' => $data['damageResistances']['PhysicalResistance']['Threshold'] ?? 0,
            'resistance_energy_multiplier' => $data['damageResistances']['EnergyResistance']['Multiplier'] ?? 0,
            'resistance_energy_threshold' => $data['damageResistances']['EnergyResistance']['Threshold'] ?? 0,
            'resistance_distortion_multiplier' => $data['damageResistances']['DistortionResistance']['Multiplier'] ?? 0,
            'resistance_distortion_threshold' => $data['damageResistances']['DistortionResistance']['Threshold'] ?? 0,
            'resistance_thermal_multiplier' => $data['damageResistances']['ThermalResistance']['Multiplier'] ?? 0,
            'resistance_thermal_threshold' => $data['damageResistances']['ThermalResistance']['Threshold'] ?? 0,
            'resistance_biochemical_multiplier' => $data['damageResistances']['BiochemicalResistance']['Multiplier'] ?? 0,
            'resistance_biochemical_threshold' => $data['damageResistances']['BiochemicalResistance']['Threshold'] ?? 0,
            'resistance_stun_multiplier' => $data['damageResistances']['StunResistance']['Multiplier'] ?? 0,
            'resistance_stun_threshold' => $data['damageResistances']['StunResistance']['Threshold'] ?? 0,
        ];
        // phpcs:enable
    }
}
