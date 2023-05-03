<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\StarCitizenUnpacked\ShipItem;

use App\Models\StarCitizenUnpacked\ShipItem\AbstractShipItemSpecification;
use App\Models\StarCitizenUnpacked\ShipItem\ShipItem;
use App\Transformers\Api\V1\StarCitizenUnpacked\AbstractCommodityTransformer;
use App\Transformers\Api\V1\StarCitizenUnpacked\PersonalInventoryTransformer;
use App\Transformers\Api\V1\StarCitizenUnpacked\ShipItem\QuantumDrive\QuantumDriveTransformer;
use App\Transformers\Api\V1\StarCitizenUnpacked\ShipItem\Shield\ShieldTransformer;
use App\Transformers\Api\V1\StarCitizenUnpacked\ShipItem\Weapon\MissileTransformer;
use App\Transformers\Api\V1\StarCitizenUnpacked\ShipItem\Weapon\WeaponModeTransformer;
use App\Transformers\Api\V1\StarCitizenUnpacked\ShipItem\Weapon\WeaponTransformer;
use App\Transformers\Api\V1\StarCitizenUnpacked\Shop\ShopTransformer;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use RuntimeException;

class ShipItemTransformer extends AbstractCommodityTransformer
{
    protected array $availableIncludes = [
        'shops',
        'heat',
        'power',
        'distortion',
        'durability',
    ];

    protected array $defaultIncludes = [
        'heat',
        'power',
        'distortion',
        'durability',
    ];

    public function transform($item): array
    {
        $item = $this->fixItem($item);

        $transformed = [
            'uuid' => $item->shipItem->item->uuid,
            'name' => $item->shipItem->item->name,
            'description' => $this->getTranslation($item->shipItem),
            'size' => $item->shipItem->item->size,
            'manufacturer' => $item->shipItem->item->manufacturer,
            'grade' => $item->shipItem->grade,
            'class' => $item->shipItem->class,
            'type' => $item->shipItem->type === 'Unknown Type' ?
                $item->shipItem->item->type :
                $item->shipItem->type,
            'sub_type' => $item->shipItem->item->sub_type,
            'health' => $item->shipItem->health,
            'volume' => [
                'width' => $item->shipItem->item->volume->width,
                'height' => $item->shipItem->item->volume->height,
                'length' => $item->shipItem->item->volume->length,
                'volume' => $item->shipItem->item->volume->volume,
            ],
            'version' => $item->shipItem->version,
        ];

        $this->addSpecificationData($item);

        return $transformed;
    }

    public function excludeDefaults(): void
    {
        $this->defaultIncludes = [];
    }

    /**
     * @param AbstractShipItemSpecification $item
     * @return Collection
     */
    public function includeShops($item): Collection
    {
        return $this->collection($this->fixItem($item)->shipItem->item->shops, new ShopTransformer());
    }

    private function addSpecificationData(AbstractShipItemSpecification $item): void
    {
        switch ($this->fixItem($item)->shipItem->item->type) {
            case 'CargoGrid':
                $this->defaultIncludes[] = 'cargoGrid';
                break;

            case 'Cooler':
                $this->defaultIncludes[] = 'cooler';
                break;

//            case 'PersonalInventory':
//                $this->defaultIncludes[] = 'personalInventory';
//                break;

            case 'PowerPlant':
                $this->defaultIncludes[] = 'powerPlant';
                break;

            case 'QuantumDrive':
                $this->defaultIncludes[] = 'quantumDrive';
                break;

            case 'Shield':
                $this->defaultIncludes[] = 'shield';
                break;

            case 'WeaponGun':
                $this->defaultIncludes[] = 'weapon';
                break;

            case 'WeaponMining':
                $this->defaultIncludes[] = 'miningLaser';
                break;

            case 'WeaponDefensive':
                $this->defaultIncludes[] = 'counterMeasure';
                break;

            case 'MissileLauncher':
                $this->defaultIncludes[] = 'missileRack';
                break;

            case 'Missile':
                $this->defaultIncludes[] = 'missile';
                break;

            case 'FuelTank':
            case 'QuantumFuelTank':
                $this->defaultIncludes[] = 'fuelTank';
                break;

            case 'FuelIntake':
                $this->defaultIncludes[] = 'fuelIntake';
                break;

            case 'MainThruster':
            case 'ManneuverThruster':
                $this->defaultIncludes[] = 'thruster';
                break;

            case 'SelfDestruct':
                $this->defaultIncludes[] = 'selfDestruct';
                break;

//            case 'ToolArm':
//            case 'Turret':
//            case 'TurretBase':
//            case 'MiningArm':
//                $this->defaultIncludes[] = 'turret';
//                break;

            case 'Radar':
                $this->defaultIncludes[] = 'radar';
                break;

            default:
                break;
        }
    }

    public function includeModes($model): Collection
    {
        return $this->collection($model->modes, new WeaponModeTransformer());
    }

    public function includeHeat($data): Item
    {
        return $this->item($this->fixItem($data)->shipItem->heatData, new ShipItemHeatDataTransformer());
    }

    public function includePower($data): Item
    {
        return $this->item($this->fixItem($data)->shipItem->powerData, new ShipItemPowerDataTransformer());
    }

    public function includeDistortion($data): Item
    {
        return $this->item($this->fixItem($data)->shipItem->distortionData, new ShipItemDistortionDataTransformer());
    }

    public function includeDurability($data): Item
    {
        return $this->item($this->fixItem($data)->shipItem->durabilityData, new ShipItemDurabilityDataTransformer());
    }

    public function includeShield($data): Item
    {
        return $this->item($this->fixItem($data), new ShieldTransformer());
    }

    public function includePowerPlant($data): Item
    {
        return $this->item($this->fixItem($data), new PowerPlantTransformer());
    }

    public function includeCooler($data): Item
    {
        return $this->item($this->fixItem($data), new CoolerTransformer());
    }

    public function includeQuantumDrive($data): Item
    {
        return $this->item($this->fixItem($data), new QuantumDriveTransformer());
    }

    public function includeWeapon($data): Item
    {
        return $this->item($this->fixItem($data), new WeaponTransformer());
    }

    public function includeMissileRack($data): Item
    {
        return $this->item($this->fixItem($data), new MissileRackTransformer());
    }

    public function includeMissile($data): Item
    {
        return $this->item($this->fixItem($data), new MissileTransformer());
    }

    public function includeFuelTank($data): Item
    {
        return $this->item($this->fixItem($data), new FuelTankTransformer());
    }

    public function includeFuelIntake($data): Item
    {
        return $this->item($this->fixItem($data), new FuelIntakeTransformer());
    }

    public function includeThruster($data): Item
    {
        return $this->item($this->fixItem($data), new ThrusterTransformer());
    }

    public function includeSelfDestruct($data): Item
    {
        return $this->item($this->fixItem($data), new SelfDestructTransformer());
    }

    public function includeTurret($data): Item
    {
        return $this->item($this->fixItem($data), new TurretTransformer());
    }

    public function includeCounterMeasure($data): Item
    {
        return $this->item($this->fixItem($data), new CounterMeasureTransformer());
    }

    public function includeRadar($data): Item
    {
        return $this->item($this->fixItem($data), new RadarTransformer());
    }

    public function includeMiningLaser($data): Item
    {
        return $this->item($this->fixItem($data), new MiningLaserTransformer());
    }

    public function includeCargoGrid($data): Item
    {
        return $this->item($this->fixItem($data), new CargoGridTransformer());
    }

    public function includePersonalInventory($data): Item
    {
        return $this->item($this->fixItem($data), new PersonalInventoryTransformer());
    }

    private function fixItem($item): AbstractShipItemSpecification
    {
        if ($item instanceof ShipItem || $item instanceof \App\Models\StarCitizenUnpacked\Item) {
            $item = $item->specification;
        }

        if (!$item instanceof AbstractShipItemSpecification) {
            throw new RuntimeException();
        }

        return $item;
    }
}
