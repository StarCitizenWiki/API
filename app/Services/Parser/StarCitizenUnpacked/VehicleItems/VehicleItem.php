<?php

declare(strict_types=1);

namespace App\Services\Parser\StarCitizenUnpacked\VehicleItems;

use App\Services\Parser\StarCitizenUnpacked\AbstractCommodityItem;
use App\Services\Parser\StarCitizenUnpacked\ItemBaseData;
use App\Services\Parser\StarCitizenUnpacked\Weapon;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

final class VehicleItem extends AbstractCommodityItem
{
    public function getData(): ?array
    {
        $attachDef = $this->getAttachDef();

        if ($attachDef === null) {
            return null;
        }

        $description = $this->getDescription($attachDef);

        $data = $this->tryExtractDataFromDescription($description, [
            'Item Type' => 'item_type',
            'Manufacturer' => 'manufacturer',
            'Size' => 'size',
            'Grade' => 'grade',
            'Class' => 'item_class',
            'Attachment Point' => 'attachment_point',
            'Missiles' => 'missiles',
            'Rockets' => 'rockets',
            'Tracking Signal' => 'tracking_signal',
        ]);

        $mappedItem = [
            'uuid' => $this->getUUID(),
            'size' => $data['size'] ?? $attachDef['Size'] ?? null,
            'description' => $data['description'] ?? null,
            'type' => trim($data['item_type'] ?? 'Unknown Type'),
            'class' => trim($data['item_class'] ?? 'Unknown Class'),
            'grade' => $data['grade'] ?? null,
        ];

        if (($mappedItem['type'] === 'Unknown Type') && isset($attachDef['Type'])) {
            $mappedItem['type'] = trim(preg_replace('/([A-Z])/', ' $1', $attachDef['Type']));
        }

        $this->addData($mappedItem, $this->item);

        return $mappedItem;
    }

    private function addData(array &$mappedItem, Collection $item): void
    {
        $mappedItem = array_merge(
            $mappedItem,
            ItemBaseData::getData($item)
        );

        $mappedItem['cargo_grid'] = CargoGrid::getData($item);
        $mappedItem['cooler'] = Cooler::getData($item);
        $mappedItem['counter_measure'] = CounterMeasure::getData($item);
        $mappedItem['emp'] = Emp::getData($item);
        $mappedItem['flight_controller'] = FlightController::getData($item);
        $mappedItem['fuel_intake'] = FuelIntake::getData($item);
        $mappedItem['fuel_tank'] = FuelTank::getData($item);
        $mappedItem['missile'] = Missile::getData($item);
        $mappedItem['missile_rack'] = MissileRack::getData($item);
        $mappedItem['power_plant'] = PowerPlant::getData($item);
        $mappedItem['qig'] = QuantumInterdictionGenerator::getData($item);
        $mappedItem['quantum_drive'] = QuantumDrive::getData($item);
        $mappedItem['radar'] = Radar::getData($item);
        $mappedItem['self_destruct'] = SelfDestruct::getData($item);
        $mappedItem['shield'] = Shield::getData($item);
        $mappedItem['thruster'] = Thruster::getData($item);
        if (Arr::has($item, 'Raw.Entity.Components.SCItemWeaponComponentParams')) {
            $mappedItem['weapon'] = (new Weapon($this->filePath, $this->labels))->getData();
        }
    }
}
