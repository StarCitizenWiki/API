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
use App\Models\StarCitizenUnpacked\ShipItem\Weapon\Weapon;
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

class HardpointTransformer extends AbstractCommodityTransformer
{
    public function transform(Hardpoint $hardpoint): array
    {
        $data = [
            'name' => $hardpoint->name,
            'min_size' => $hardpoint->hardpoint_data->min_size,
            'max_size' => $hardpoint->hardpoint_data->max_size,
        ];

        if ($hardpoint->hardpoint_data->children->isNotEmpty()) {
            $this->defaultIncludes[] = 'children';
        }

        if ($hardpoint->hardpoint_data->equipped_vehicle_item_uuid !== null) {
            $this->defaultIncludes[] = 'item';
        }

        return $data;
    }

    public function includeChildren($hardpoint): Collection
    {
        return $this->collection($hardpoint->hardpoint_data->children, new VehicleHardpointTransformer());
    }

    public function includeItem(Hardpoint $item)
    {
        if ($item->hardpoint_data->item === null) {
            return $this->null();
        }

        return $this->item($item->hardpoint_data->item->itemSpecification, new ShipItemTransformer());
    }
}
