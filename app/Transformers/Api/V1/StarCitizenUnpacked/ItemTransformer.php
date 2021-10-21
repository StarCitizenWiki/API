<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\StarCitizenUnpacked;

use App\Models\StarCitizenUnpacked\CharArmor\CharArmor;
use App\Models\StarCitizenUnpacked\Item;
use App\Models\StarCitizenUnpacked\ShipItem\Cooler;
use App\Models\StarCitizenUnpacked\ShipItem\PowerPlant;
use App\Models\StarCitizenUnpacked\ShipItem\QuantumDrive\QuantumDrive;
use App\Models\StarCitizenUnpacked\ShipItem\Shield\Shield;
use App\Models\StarCitizenUnpacked\ShipItem\Weapon\Weapon;
use App\Models\StarCitizenUnpacked\WeaponPersonal\WeaponPersonal;
use App\Models\StarCitizenUnpacked\WeaponPersonal\WeaponPersonalAttachment;
use App\Transformers\Api\V1\StarCitizenUnpacked\CharArmor\CharArmorTransformer;
use App\Transformers\Api\V1\StarCitizenUnpacked\ShipItem\QuantumDrive\ShipQuantumDriveTransformer;
use App\Transformers\Api\V1\StarCitizenUnpacked\ShipItem\Shield\ShipShieldTransformer;
use App\Transformers\Api\V1\StarCitizenUnpacked\ShipItem\ShipCoolerTransformer;
use App\Transformers\Api\V1\StarCitizenUnpacked\ShipItem\ShipPowerPlantTransformer;
use App\Transformers\Api\V1\StarCitizenUnpacked\ShipItem\Weapon\ShipWeaponTransformer;
use App\Transformers\Api\V1\StarCitizenUnpacked\WeaponPersonal\WeaponPersonalAttachmentsTransformer;
use App\Transformers\Api\V1\StarCitizenUnpacked\WeaponPersonal\WeaponPersonalTransformer;
use League\Fractal\Resource\ResourceAbstract;

class ItemTransformer extends AbstractCommodityTransformer
{
    protected $availableIncludes = [
        'shops'
    ];

    public function transform(Item $item): array
    {
        if ($item->specification !== null) {
            $this->defaultIncludes[] = 'specification';
        }

        return [
            'uuid' => $item->uuid,
            'name' => $item->name,
            'type' => $item->type,
            'sub_type' => $item->sub_type,
            'manufacturer' => $item->manufacturer,
            'size' => $item->size,
            'version' => $item->version,
        ];
    }

    public function includeSpecification(Item $item): ResourceAbstract
    {
        switch ($item->specification !== null ? get_class($item->specification) : '') {
            case CharArmor::class:
                return $this->item($item->specification, new CharArmorTransformer());

            case WeaponPersonal::class:
                return $this->item($item->specification, new WeaponPersonalTransformer());

            case WeaponPersonalAttachment::class:
                return $this->item($item->specification, new WeaponPersonalAttachmentsTransformer());

            case Weapon::class:
                return $this->item($item->specification, new ShipWeaponTransformer());

            case Cooler::class:
                return $this->item($item->specification, new ShipCoolerTransformer());

            case QuantumDrive::class:
                return $this->item($item->specification, new ShipQuantumDriveTransformer());

            case PowerPlant::class:
                return $this->item($item->specification, new ShipPowerPlantTransformer());

            case Shield::class:
                return $this->item($item->specification, new ShipShieldTransformer());

            default:
                return $this->null();
        }
    }
}
