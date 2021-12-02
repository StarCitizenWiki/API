<?php

declare(strict_types=1);

namespace App\Services\Parser\StarCitizenUnpacked\ShipItems;

use App\Services\Parser\StarCitizenUnpacked\AbstractCommodityItem;
use App\Services\Parser\StarCitizenUnpacked\Labels;
use App\Services\Parser\StarCitizenUnpacked\Manufacturers;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;

final class ShipItem extends AbstractCommodityItem
{
    private Collection $items;

    private $labels;
    private $manufacturers;

    public function __construct()
    {
        $this->labels = (new Labels())->getData();
        $this->manufacturers = (new Manufacturers())->getData();
    }

    /**
     * @throws FileNotFoundException
     * @throws \JsonException
     */
    public function loadFromShipItems(): void
    {
        $items = File::get(storage_path('app/api/scunpacked-data/ship-items.json'));
        $this->items = collect(json_decode($items, true, 512, JSON_THROW_ON_ERROR));
    }

    public function setItems(Collection $items): void
    {
        $this->items = $items;
    }

    public function getData(bool $onlyBaseVersions = false, bool $excludeToy = true): Collection
    {
        return $this->items
            ->filter(function (array $entry) {
                return isset($entry['reference']) || isset($entry['__ref']);
            })
            ->filter(function (array $entry) {
                $className = $entry['ClassName'] ?? $entry['className'] ?? '';
                return !empty($className) && strpos($className, 'test_') === false;
            })
            ->filter(function (array $entry) {
                $type = $entry['Components']['SAttachableComponentParams']['AttachDef']['Type'] ?? $entry['type'] ?? '';

                return !empty($type) &&
                    $type !== 'Armor' &&
                    $type !== 'Ping' &&
                    $type !== 'WeaponDefensive' &&
                    $type !== 'Paints';
            })
            ->map(function (array $entry) {
                $out = $entry['stdItem'] ?? $entry;
                $out['reference'] = $entry['reference'] ?? $entry['__ref'] ?? null;
                $out['itemName'] = $entry['itemName'] ?? $entry['ClassName'] ?? null;

                return $out;
            })
            /*            ->filter(function (array $entry) {
                            return isset($entry['Description']) && !empty($entry) && !empty($entry['Description']);
                        })*/
            /*            ->filter(function (array $entry) {
                            return isset($entry['Durability']);
                        })*/
            ->map(function (array $entry) {
                $item = File::get(
                    storage_path(
                        sprintf(
                            'app/api/scunpacked-data/v2/items/%s-raw.json',
                            strtolower($entry['itemName'])
                        )
                    )
                );

                $rawData = collect(json_decode($item, true, 512, JSON_THROW_ON_ERROR));
                if (!isset($entry['Description']) || empty($entry['Description'])) {
                    $entry['Description'] = $this->labels->get(substr($rawData['Components']['SAttachableComponentParams']['AttachDef']['Localization']['Description'], 1));
                }

                return $this->map($entry, $rawData);
            })
            ->filter(function ($entry) {
                return $entry !== null;
            })
            ->unique('name');
    }

    private function map(array $item, Collection $rawData): ?array
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

        if (!isset($item['Description']) || empty($item['Description'])) {
            return null;
        }

        $item['Description'] = str_replace(["\n", '\n'], "\n", $item['Description']);

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
            'uuid' => $item['__ref'] ?? $item['reference'],
            'size' => $item['Components']['SAttachableComponentParams']['AttachDef']['Size'] ?? $item['Size'] ?? 0,
            'item_type' => $item['Components']['SAttachableComponentParams']['AttachDef']['Type'] ?? $item['Type'] ?? 0,
            'item_class' => $item['Components']['SAttachableComponentParams']['AttachDef']['Class'] ?? trim($item['Classification'] ?? 'Unknown Class'),
            'item_grade' => $item['Components']['SAttachableComponentParams']['AttachDef']['Grade'] ?? $item['Grade'] ?? 0,
            'description' => $data['description'] ?? '',
            'name' => str_replace(
                [
                    '“',
                    '”',
                    '"',
                    '\'',
                ],
                '"',
                trim($item['Name'] ?? $this->labels->get(substr($item['Components']['SAttachableComponentParams']['AttachDef']['Localization']['Name'], 1)) ?? 'Unknown Ship Item')
            ),
            'manufacturer' => $data['manufacturer'] ?? $this->getManufacturer($item),
            'type' => trim($data['item_type'] ?? 'Unknown Type'),
            'class' => trim($data['item_class'] ?? 'Unknown Class'),
            'grade' => $data['grade'] ?? null,
        ];

        $this->addData($mappedItem, $item, $rawData);

        return $mappedItem;
    }

    private function addData(array &$mappedItem, array $item, Collection $rawData): void
    {
        $mappedItem = array_merge(
            $mappedItem,
            BaseData::getData($item, $rawData)
        );
        $mappedItem['cooler'] = Cooler::getData($item, $rawData);
        $mappedItem['power_plant'] = PowerPlant::getData($item, $rawData);
        $mappedItem['shield'] = Shield::getData($item, $rawData);
        $mappedItem['quantum_drive'] = QuantumDrive::getData($item, $rawData);
        $mappedItem['weapon'] = Weapon::getData($item, $rawData);
        $mappedItem['missile_rack'] = MissileRack::getData($item, $rawData);
        $mappedItem['missile'] = Missile::getData($item, $rawData);
        $mappedItem['turret'] = Turret::getData($item, $rawData);
    }
}
