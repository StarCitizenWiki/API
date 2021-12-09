<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\StarCitizenUnpacked;

use App\Models\StarCitizenUnpacked\CharArmor\CharArmor;
use App\Models\StarCitizenUnpacked\Hardpoint;
use App\Models\StarCitizenUnpacked\Item;
use App\Models\StarCitizenUnpacked\ShipItem\Cooler;
use App\Models\StarCitizenUnpacked\ShipItem\PowerPlant;
use App\Models\StarCitizenUnpacked\ShipItem\QuantumDrive\QuantumDrive;
use App\Models\StarCitizenUnpacked\ShipItem\Shield\Shield;
use App\Models\StarCitizenUnpacked\ShipItem\ShipItem;
use App\Models\StarCitizenUnpacked\ShipItem\Weapon\Missile;
use App\Models\StarCitizenUnpacked\VehicleHardpoint;
use App\Models\StarCitizenUnpacked\WeaponPersonal\WeaponPersonal;
use App\Models\StarCitizenUnpacked\WeaponPersonal\WeaponPersonalAttachment;
use App\Transformers\Api\V1\StarCitizenUnpacked\CharArmor\CharArmorTransformer;
use App\Transformers\Api\V1\StarCitizenUnpacked\ShipItem\QuantumDrive\ShipQuantumDriveTransformer;
use App\Transformers\Api\V1\StarCitizenUnpacked\ShipItem\Shield\ShipShieldTransformer;
use App\Transformers\Api\V1\StarCitizenUnpacked\ShipItem\ShipCoolerTransformer;
use App\Transformers\Api\V1\StarCitizenUnpacked\ShipItem\ShipItemTransformer;
use App\Transformers\Api\V1\StarCitizenUnpacked\ShipItem\ShipPowerPlantTransformer;
use App\Transformers\Api\V1\StarCitizenUnpacked\ShipItem\Weapon\ShipWeaponTransformer;
use App\Transformers\Api\V1\StarCitizenUnpacked\WeaponPersonal\WeaponPersonalAttachmentsTransformer;
use App\Transformers\Api\V1\StarCitizenUnpacked\WeaponPersonal\WeaponPersonalTransformer;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\ResourceAbstract;

class VehicleHardpointTransformer extends AbstractCommodityTransformer
{

    public function transform(VehicleHardpoint $hardpoint): array
    {
        $data = [
            'name' => $hardpoint->hardpoint->name,
        ];

        if ($hardpoint->item !== null && $hardpoint->item->exists()) {
            $this->defaultIncludes[] = 'item';
        }

        if ($hardpoint->children->isNotEmpty()) {
            $this->defaultIncludes[] = 'children';
        }

        return $data;
    }

    public function includeItem(VehicleHardpoint $item): \League\Fractal\Resource\Item
    {
        return $this->item($item->item->itemSpecification, new ShipItemTransformer());
    }

    public function includeChildren($hardpoint): Collection
    {
        return $this->collection($hardpoint->children, new self());
    }
}
