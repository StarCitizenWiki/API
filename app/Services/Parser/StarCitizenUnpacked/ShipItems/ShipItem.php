<?php

declare(strict_types=1);

namespace App\Services\Parser\StarCitizenUnpacked\ShipItems;

use App\Services\Parser\StarCitizenUnpacked\AbstractCommodityItem;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;

final class ShipItem extends AbstractCommodityItem
{
    private Collection $items;

    /**
     * AssaultRifle constructor.
     * @throws FileNotFoundException
     * @throws JsonException
     */
    public function __construct()
    {
        $items = File::get(storage_path(sprintf('app/api/scunpacked-data/ship-items.json')));
        $this->items = collect(json_decode($items, true, 512, JSON_THROW_ON_ERROR));
    }


    public function getData(bool $onlyBaseVersions = false, bool $excludeToy = true): Collection
    {
        return $this->items
            ->filter(function (array $entry) {
                return isset($entry['reference']);
            })
            ->filter(function (array $entry) {
                return isset($entry['className']) && strpos($entry['className'], 'test_') === false;
            })
            ->filter(function (array $entry) {
                return isset($entry['type']) &&
                    $entry['type'] !== 'Armor' &&
                    $entry['type'] !== 'Ping' &&
                    $entry['type'] !== 'WeaponDefensive' &&
                    $entry['type'] !== 'Paints' &&
                    $entry['type'] !== 'Radar';
            })
            ->map(function (array $entry) {
                $out = $entry['stdItem'] ?? [];
                $out['reference'] = $entry['reference'] ?? null;

                return $out;
            })
            ->filter(function (array $entry) {
                return isset($entry['Description']) && !empty($entry) && !empty($entry['Description']);
            })
            ->filter(function (array $entry) {
                return isset($entry['Durability']);
            })
            ->map(function (array $entry) {
                return $this->map($entry);
            })
            ->unique('name');
    }

    private function map(array $item): array
    {
        /**
         * BASE ALL
         *  Durability
         *      Health
         *      Lifetime
         *  PowerConnection
         *      PowerBase
         *      PowerDraw
         *  HeatConnection
         *      ThermalEnergyBase
         *      ThermalEnergyDraw
         *      CoolingRate
         *
         *
         * Cooler:
         *  Cooler
         *      Rate
         *
         *
         * PowerPlanet
         *  PowerPlant
         *      Output
         *
         *
         * QuantumDrive
         *  QuantumDrive
         *      FuelRate
         *      JumpRange
         *      StandardJump
         *          Speed
         *          Cooldown
         *          Stage1AccelerationRate
         *          Stage2AccelerationRate
         *          SpoolTime
         *      SplineJump
         *          Speed
         *          Cooldown
         *          Stage1AccelerationRate
         *          Stage2AccelerationRate
         *          SpoolTime
         *
         *
         * QuantumInterdictionGenerator
         *  QuantumInterdictionGenerator
         *      JammingRange
         *      JumpRange
         *      InterdictionRange
         *
         * Shield
         *  Shield
         *      Health
         *      Regeneration
         *      DownedDelay
         *      DamageDelay
         *      Absorption
         *          Physical
         *              Min
         *              Max
         *          Energy
         *              Min
         *              Max
         *          Distortion
         *              Min
         *              Max
         *          Thermal
         *              Min
         *              Max
         *          Biochemical
         *              Min
         *              Max
         *          Stun
         *              Min
         *              Max
         *
         * Weapon
         *  Weapon
         *      Ammunition
         *          Speed
         *          Range
         *          Size
         *          Capacity
         *          ImpactDamage
         *              Energy
         *              Physical
         *              Distortion
         *      Modes
         *          Name
         *          RoundsPerMinute
         *          FireType
         *          AmmoPerShot
         *          PelletsPerShot
         *          DamagePerShot
         *              Energy
         *              Physical
         *              Distortion
         *          DamagePerSecond
         *              Energy
         *              Physical
         *              Distortion
         *  Ammunition
         *      Speed
         *      Range
         *      Size
         *      Capacity
         *      ImpactDamage
         *          Energy
         *      DetonationDamage
         *
         *
         * MissileLauncher
         */

        $data = $this->tryExtractDataFromDescription($item['Description'], [
            'Item Type' => 'item_type',
            'Manufacturer' => 'manufacturer',
            'Size' => 'size',
            'Grade' => 'grade',
            'Class' => 'item_class',
            'Attachment Point' => 'attachment_point',
            'Missiles' => 'misslies',
            'Rockets' => 'rockets',
            'Tracking Signal' => 'tracking_signal',
        ]);

        $mappedItem = [
            'uuid' => $item['reference'],
            'size' => $item['Size'] ?? 0,
            'item_type' => $item['Type'] ?? 0,
            'item_class' => trim($item['Classification'] ?? 'Unknown Class'),
            'item_grade' => $item['Grade'] ?? 0,
            'description' => $data['description'] ?? '',
            'name' => str_replace(
                [
                    '“',
                    '”',
                    '"',
                    '\'',
                ],
                '"',
                trim($item['Name'] ?? 'Unknown Ship Item')
            ),
            'manufacturer' => $this->getManufacturer($item),
            'type' => trim($data['item_type'] ?? 'Unknown Type'),
            'class' => trim($data['item_class'] ?? 'Unknown Type'),
            'grade' => $data['grade'] ?? null,
        ];

        $this->addData($mappedItem, $item);

        return $mappedItem;
    }

    private function addData(array &$mappedItem, array $item): void
    {
        $this->addBaseData($mappedItem, $item);
        $this->addCoolerData($mappedItem, $item);
        $this->addPowerPlantData($mappedItem, $item);
        $this->addShieldData($mappedItem, $item);
        $this->addQuantumDriveData($mappedItem, $item);
    }

    private function addBaseData(array &$mappedItem, array $item): void
    {
        $mappedItem['durability'] = [
            'health' => $item['Durability']['Health'] ?? 0,
            'lifetime' => $item['Durability']['Lifetime'] ?? 0,
        ];

        $mappedItem['power'] = [
            'power_base' => $item['PowerConnection']['PowerBase'] ?? 0,
            'power_draw' => $item['PowerConnection']['PowerDraw'] ?? 0,
        ];

        $mappedItem['thermal'] = [
            'thermal_energy_base' => $item['HeatConnection']['ThermalEnergyBase'] ?? 0,
            'thermal_energy_draw' => $item['HeatConnection']['ThermalEnergyDraw'] ?? 0,
            'cooling_rate' => $item['HeatConnection']['CoolingRate'] ?? 0,
        ];
    }

    private function addCoolerData(array &$mappedItem, array $item): void
    {
        if (!isset($item['Cooler'])) {
            return;
        }

        $mappedItem['cooler'] = [
            'cooling_rate' => $item['Cooler']['Rate'],
        ];
    }

    private function addPowerPlantData(array &$mappedItem, array $item): void
    {
        if (!isset($item['PowerPlant'])) {
            return;
        }

        $mappedItem['power_plant'] = [
            'power_output' => $item['PowerPlant']['Output'],
        ];
    }

    private function addShieldData(array &$mappedItem, array $item): void
    {
        if (!isset($item['Shield'])) {
            return;
        }

        $mappedItem['shield'] = [
            'health' => $item['Shield']['Health'],
            'regeneration' => $item['Shield']['Regeneration'],
            'downed_delay' => $item['Shield']['DownedDelay'],
            'damage_delay' => $item['Shield']['DamagedDelay'],
            'min_physical_absorption' => $item['Shield']['Absorption']['Physical']['Minimum'],
            'max_physical_absorption' => $item['Shield']['Absorption']['Physical']['Maximum'],
            'min_energy_absorption' => $item['Shield']['Absorption']['Energy']['Minimum'],
            'max_energy_absorption' => $item['Shield']['Absorption']['Energy']['Maximum'],
            'min_distortion_absorption' => $item['Shield']['Absorption']['Distortion']['Minimum'],
            'max_distortion_absorption' => $item['Shield']['Absorption']['Distortion']['Maximum'],
            'min_thermal_absorption' => $item['Shield']['Absorption']['Thermal']['Minimum'],
            'max_thermal_absorption' => $item['Shield']['Absorption']['Thermal']['Maximum'],
            'min_biochemical_absorption' => $item['Shield']['Absorption']['Biochemical']['Minimum'],
            'max_biochemical_absorption' => $item['Shield']['Absorption']['Biochemical']['Maximum'],
            'min_stun_absorption' => $item['Shield']['Absorption']['Stun']['Minimum'],
            'max_stun_absorption' => $item['Shield']['Absorption']['Stun']['Maximum'],
        ];
    }

    private function addQuantumDriveData(array &$mappedItem, array $item): void
    {
        if (!isset($item['QuantumDrive'])) {
            return;
        }

        $mappedItem['quantum_drive'] = [
            'fuel_rate' => $item['QuantumDrive']['FuelRate'],
            'jump_range' => $item['QuantumDrive']['JumpRange'],
            'standard_speed' => $item['QuantumDrive']['StandardJump']['Speed'],
            'standard_cooldown' => $item['QuantumDrive']['StandardJump']['Cooldown'],
            'standard_stage_1_acceleration' => $item['QuantumDrive']['StandardJump']['Stage1AccelerationRate'],
            'standard_stage_2_acceleration' => $item['QuantumDrive']['StandardJump']['State2AccelerationRate'],
            'standard_spool_time' => $item['QuantumDrive']['StandardJump']['SpoolUpTime'],
            'spline_speed' => $item['QuantumDrive']['SplineJump']['Speed'],
            'spline_cooldown' => $item['QuantumDrive']['SplineJump']['Cooldown'],
            'spline_stage_1_acceleration' => $item['QuantumDrive']['SplineJump']['Stage1AccelerationRate'],
            'spline_stage_2_acceleration' => $item['QuantumDrive']['SplineJump']['State2AccelerationRate'],
            'spline_spool_time' => $item['QuantumDrive']['SplineJump']['SpoolUpTime'],
        ];
    }
}
