<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\StarCitizenUnpacked;

use App\Models\StarCitizenUnpacked\CharArmor\CharArmor;
use App\Models\StarCitizenUnpacked\Clothing;
use App\Models\StarCitizenUnpacked\Item;
use App\Models\StarCitizenUnpacked\ShipItem\Cooler;
use App\Models\StarCitizenUnpacked\ShipItem\MiningLaser;
use App\Models\StarCitizenUnpacked\ShipItem\PowerPlant;
use App\Models\StarCitizenUnpacked\ShipItem\QuantumDrive\QuantumDrive;
use App\Models\StarCitizenUnpacked\ShipItem\Shield\Shield;
use App\Models\StarCitizenUnpacked\ShipItem\Weapon\Missile;
use App\Models\StarCitizenUnpacked\ShipItem\Weapon\Weapon;
use App\Models\StarCitizenUnpacked\Turret;
use App\Models\StarCitizenUnpacked\WeaponPersonal\WeaponPersonal;
use App\Models\StarCitizenUnpacked\WeaponPersonal\WeaponPersonalAttachment;
use App\Transformers\Api\V1\StarCitizenUnpacked\CharArmor\CharArmorTransformer;
use App\Transformers\Api\V1\StarCitizenUnpacked\ShipItem\CoolerTransformer;
use App\Transformers\Api\V1\StarCitizenUnpacked\ShipItem\MiningLaserTransformer;
use App\Transformers\Api\V1\StarCitizenUnpacked\ShipItem\PowerPlantTransformer;
use App\Transformers\Api\V1\StarCitizenUnpacked\ShipItem\QuantumDrive\QuantumDriveTransformer;
use App\Transformers\Api\V1\StarCitizenUnpacked\ShipItem\Shield\ShieldTransformer;
use App\Transformers\Api\V1\StarCitizenUnpacked\ShipItem\TurretTransformer;
use App\Transformers\Api\V1\StarCitizenUnpacked\ShipItem\Weapon\WeaponTransformer;
use App\Transformers\Api\V1\StarCitizenUnpacked\WeaponPersonal\WeaponPersonalAttachmentsTransformer;
use App\Transformers\Api\V1\StarCitizenUnpacked\WeaponPersonal\WeaponPersonalTransformer;
use League\Fractal\Resource\ResourceAbstract;

/**
 * Generic transformer for all items
 * Includes the item specification if one exists
 */
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
            'volume' => [
                'width' => $item->volume->width ?? 0,
                'height' => $item->volume->height ?? 0,
                'length' => $item->volume->length ?? 0,
                'volume' => $item->volume->volume ?? 0,
            ],
            'version' => $item->version,
        ];
    }

    public function includeSpecification(Item $item): ResourceAbstract
    {
        switch ($item->specification !== null ? get_class($item->specification) : '') {
            case CharArmor::class:
                return $this->item($item->specification, new CharArmorTransformer());

            case Clothing::class:
                return $this->item($item->specification, new ClothingTransformer());

            case WeaponPersonal::class:
                return $this->item($item->specification, new WeaponPersonalTransformer());

            case WeaponPersonalAttachment::class:
                return $this->item($item->specification, new WeaponPersonalAttachmentsTransformer());

            case Missile::class:
                return $this->item($item->specification, new WeaponTransformer());

            case Cooler::class:
                return $this->item($item->specification, new CoolerTransformer());

            case QuantumDrive::class:
                return $this->item($item->specification, new QuantumDriveTransformer());

            case PowerPlant::class:
                return $this->item($item->specification, new PowerPlantTransformer());

            case Shield::class:
                return $this->item($item->specification, new ShieldTransformer());

            case Weapon::class:
                return $this->item($item->specification(), new WeaponTransformer());

            case MiningLaser::class:
                return $this->item($item->specification(), new MiningLaserTransformer());

            case Turret::class:
                return $this->item($item->specification(), new TurretTransformer());

            default:
                return $this->null();
        }
    }
}
