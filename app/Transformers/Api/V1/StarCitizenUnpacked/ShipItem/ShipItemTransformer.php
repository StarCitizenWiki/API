<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\StarCitizenUnpacked\ShipItem;

use App\Models\StarCitizenUnpacked\ShipItem\AbstractShipItemSpecification;
use App\Transformers\Api\V1\StarCitizenUnpacked\AbstractCommodityTransformer;
use App\Transformers\Api\V1\StarCitizenUnpacked\ShipItem\QuantumDrive\ShipQuantumDriveTransformer;
use App\Transformers\Api\V1\StarCitizenUnpacked\ShipItem\Shield\ShipShieldTransformer;
use App\Transformers\Api\V1\StarCitizenUnpacked\ShipItem\Weapon\ShipWeaponModeTransformer;
use App\Transformers\Api\V1\StarCitizenUnpacked\ShipItem\Weapon\ShipWeaponTransformer;
use App\Transformers\Api\V1\StarCitizenUnpacked\Shop\ShopTransformer;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;

class ShipItemTransformer extends AbstractCommodityTransformer
{
    protected $availableIncludes = [
        'shops',
    ];

    protected $defaultIncludes = [
        'heat',
        'power',
        'distortion',
        'durability',
    ];

    public function transform(AbstractShipItemSpecification $item): array
    {
        $transformed = [
            'uuid' => $item->shipItem->item->uuid,
            'name' => $item->shipItem->item->name,
            'description' => $this->getTranslation($item->shipItem),
            'size' => $item->shipItem->item->size,
            'manufacturer' => $item->shipItem->item->manufacturer,
            'grade' => $item->shipItem->grade,
            'class' => $item->shipItem->class,
            'type' => $item->shipItem->type,
            'durability' => [
                'health' => $item->shipItem->health,
                'lifetime' => $item->shipItem->lifetime,
            ],
            'version' => $item->shipItem->version,
        ];

        $this->addSpecificationData($item);

        return $transformed;
    }

    /**
     * @param AbstractShipItemSpecification $item
     * @return Collection
     */
    public function includeShops($item): Collection
    {
        return $this->collection($item->shipItem->item->shops, new ShopTransformer());
    }

    private function addSpecificationData(AbstractShipItemSpecification $item): void
    {
        switch ($item->shipItem->item->type) {
            case 'Cooler':
                $this->defaultIncludes[] = 'cooler';
                break;

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

            default:
                break;
        }
    }

    public function includeModes($model): Collection
    {
        return $this->collection($model->modes, new ShipWeaponModeTransformer());
    }

    public function includeHeat(AbstractShipItemSpecification $data): Item
    {
        return $this->item($data->shipItem->heatData, new ShipItemHeatDataTransformer());
    }

    public function includePower(AbstractShipItemSpecification $data): Item
    {
        return $this->item($data->shipItem->powerData, new ShipItemPowerDataTransformer());
    }

    public function includeDistortion(AbstractShipItemSpecification $data): Item
    {
        return $this->item($data->shipItem->distortionData, new ShipItemDistortionDataTransformer());
    }

    public function includeDurability(AbstractShipItemSpecification $data): Item
    {
        return $this->item($data->shipItem->durabilityData, new ShipItemDurabilityDataTransformer());
    }

    public function includeShield(AbstractShipItemSpecification $data): Item
    {
        return $this->item($data, new ShipShieldTransformer());
    }

    public function includePowerPlant(AbstractShipItemSpecification $data): Item
    {
        return $this->item($data, new ShipPowerPlantTransformer());
    }

    public function includeCooler(AbstractShipItemSpecification $data): Item
    {
        return $this->item($data, new ShipCoolerTransformer());
    }

    public function includeQuantumDrive(AbstractShipItemSpecification $data): Item
    {
        return $this->item($data, new ShipQuantumDriveTransformer());
    }

    public function includeWeapon(AbstractShipItemSpecification $data): Item
    {
        return $this->item($data, new ShipWeaponTransformer());
    }
}
