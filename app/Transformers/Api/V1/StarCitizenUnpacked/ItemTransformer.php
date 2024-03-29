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
use App\Models\StarCitizenUnpacked\WeaponPersonal\Attachment;
use App\Models\StarCitizenUnpacked\WeaponPersonal\WeaponPersonal;
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
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'item',
    title: 'Item',
    description: 'An in-game item',
    properties: [
        new OA\Property(property: 'uuid', type: 'string'),
        new OA\Property(property: 'name', type: 'string'),
        new OA\Property(property: 'type', type: 'string'),
        new OA\Property(property: 'sub_type', type: 'string'),
        new OA\Property(property: 'manufacturer', type: 'string'),
        new OA\Property(property: 'size', type: 'float'),
        new OA\Property(
            property: 'volume',
            properties: [
                new OA\Property(property: 'width', type: 'float'),
                new OA\Property(property: 'height', type: 'float'),
                new OA\Property(property: 'length', type: 'float'),
                new OA\Property(property: 'volume', type: 'float'),
            ],
            nullable: true,
        ),
        new OA\Property(property: 'version', type: 'string'),
        new OA\Property(property: 'specification', type: 'object', nullable: true),
    ],
    type: 'object'
)]
class ItemTransformer extends AbstractCommodityTransformer
{
    protected array $availableIncludes = [
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
                return $this->item($item->specification, $this->makeTransformer(CharArmorTransformer::class, $this));

            case Clothing::class:
                return $this->item($item->specification, $this->makeTransformer(ClothingTransformer::class, $this));

            case WeaponPersonal::class:
                return $this->item($item->specification, $this->makeTransformer(WeaponPersonalTransformer::class, $this));

            case Attachment::class:
                return $this->item($item->specification, $this->makeTransformer(WeaponPersonalAttachmentsTransformer::class, $this));

            case Missile::class:
                return $this->item($item->specification, $this->makeTransformer(WeaponTransformer::class, $this));

            case Cooler::class:
                return $this->item($item->specification, $this->makeTransformer(CoolerTransformer::class, $this));

            case QuantumDrive::class:
                return $this->item($item->specification, $this->makeTransformer(QuantumDriveTransformer::class, $this));

            case PowerPlant::class:
                return $this->item($item->specification, $this->makeTransformer(PowerPlantTransformer::class, $this));

            case Shield::class:
                return $this->item($item->specification, $this->makeTransformer(ShieldTransformer::class, $this));

            case Weapon::class:
                return $this->item($item->specification(), $this->makeTransformer(WeaponTransformer::class, $this));

            case MiningLaser::class:
                return $this->item($item->specification(), $this->makeTransformer(MiningLaserTransformer::class, $this));

            case Turret::class:
                return $this->item($item->specification(), $this->makeTransformer(TurretTransformer::class, $this));

            default:
                return $this->null();
        }
    }
}
