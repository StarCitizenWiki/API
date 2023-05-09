<?php

declare(strict_types=1);

namespace App\Http\Resources\SC\Item;

use App\Http\Resources\AbstractTranslationResource;
use App\Http\Resources\SC\Char\ClothingResource;
use App\Http\Resources\SC\Char\GrenadeResource;
use App\Http\Resources\SC\Char\IronSightResource;
use App\Http\Resources\SC\Char\PersonalWeaponMagazineResource;
use App\Http\Resources\SC\Char\PersonalWeaponResource;
use App\Http\Resources\SC\FoodResource;
use App\Http\Resources\SC\ItemSpecification\CoolerResource;
use App\Http\Resources\SC\ItemSpecification\FlightControllerResource;
use App\Http\Resources\SC\ItemSpecification\MiningLaser\MiningLaserResource;
use App\Http\Resources\SC\ItemSpecification\MiningModuleResource;
use App\Http\Resources\SC\ItemSpecification\MissileResource;
use App\Http\Resources\SC\ItemSpecification\PowerPlantResource;
use App\Http\Resources\SC\ItemSpecification\QuantumDrive\QuantumDriveResource;
use App\Http\Resources\SC\ItemSpecification\SelfDestructResource;
use App\Http\Resources\SC\ItemSpecification\ShieldResource;
use App\Http\Resources\SC\ItemSpecification\ThrusterResource;
use App\Http\Resources\SC\ManufacturerResource;
use App\Http\Resources\SC\Shop\ShopResource;
use App\Http\Resources\SC\Vehicle\VehicleWeaponResource;
use Illuminate\Http\Request;

class ItemResource extends AbstractTranslationResource
{
    public static function validIncludes(): array
    {
        return [
            'shops',
            'shops.items'
        ];
    }

    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        if ($this->uuid === null) {
            return [];
        }

        return [
            'uuid' => $this->uuid,
            'name' => $this->name,
            'description' => $this->getTranslation($this, $request),
            'size' => $this->size,
            $this->mergeWhen($this->vehicleItem->exists, [
                'grade' => $this->vehicleItem->grade,
                'class' => $this->vehicleItem->class,
            ]),
            'manufacturer_description' => $this->manufacturer_description,
            'manufacturer' => new ManufacturerResource($this->manufacturer),
            'type' => $this->type,
            'sub_type' => $this->sub_type,
            $this->mergeWhen($this->isTurret(), $this->addTurretData()),
            $this->mergeWhen(...$this->addSpecification()),
            'dimension' => new ItemDimensionResource($this),
            $this->mergeWhen($this->container->exists, [
                'inventory' => new ItemContainerResource($this->container),
            ]),
            'ports' => ItemPortResource::collection($this->whenLoaded('ports')),
            $this->mergeWhen($this->relationLoaded('heatData'), [
                'heat' => new ItemHeatDataResource($this->heatData),
            ]),
            $this->mergeWhen($this->relationLoaded('powerData'), [
                'power' => new ItemPowerDataResource($this->powerData),
            ]),
            $this->mergeWhen($this->relationLoaded('distortionData'), [
                'distortion' => new ItemDistortionDataResource($this->distortionData),
            ]),
            $this->mergeWhen($this->relationLoaded('durabilityData'), [
                'durability' => new ItemDurabilityDataResource($this->durabilityData),
            ]),
            'shops' => ShopResource::collection($this->whenLoaded('shops')),
            'updated_at' => $this->updated_at,
            'version' => $this->version,
        ];
    }

    private function addSpecification(): array
    {
        if ($this->specification === null) {
            return [false, []];
        }

        return match (true) {
            $this->type === 'Cooler' => [
                $this->specification->exists,
                new CoolerResource($this->specification),
            ],
            str_contains($this->type, 'Char_Clothing'), str_contains($this->type, 'Char_Armor') => [
                $this->specification->exists,
                new ClothingResource($this->specification),
            ],
            $this->type === 'Food' => [
                $this->specification->exists,
                new FoodResource($this->specification),
            ],
            $this->type === 'MainThruster', $this->type === 'ManneuverThruster' => [
                $this->specification->exists,
                new ThrusterResource($this->specification),
            ],
            $this->type === 'PowerPlant' => [
                $this->specification->exists,
                new PowerPlantResource($this->specification),
            ],
            $this->type === 'Shield' => [
                $this->specification->exists,
                new ShieldResource($this->specification),
            ],
            $this->type === 'SelfDestruct' => [
                $this->specification->exists,
                new SelfDestructResource($this->specification),
            ],
            $this->type === 'FlightController' => [
                $this->specification->exists,
                new FlightControllerResource($this->specification),
            ],
            $this->type === 'QuantumDrive' => [
                $this->specification->exists,
                new QuantumDriveResource($this->specification),
            ],
            $this->type === 'WeaponPersonal' && $this->sub_type === 'Grenade' => [
                $this->specification->exists,
                new GrenadeResource($this->specification),
            ],
            $this->type === 'WeaponPersonal' => [
                $this->specification->exists,
                new PersonalWeaponResource($this->specification),
            ],
            $this->sub_type === 'IronSight' => [
                $this->specification->exists,
                new IronSightResource($this->specification),
            ],
            $this->sub_type === 'Magazine' => [
                $this->specification->exists,
                new PersonalWeaponMagazineResource($this->specification),
            ],
            $this->type === 'Missile' => [
                $this->specification->exists,
                new MissileResource($this->specification),
            ],
            $this->type === 'MiningModifier' => [
                $this->specification->exists,
                new MiningModuleResource($this->specification),
            ],
            $this->type === 'WeaponGun', $this->type === 'WeaponDefensive' => [
                $this->specification->exists,
                new VehicleWeaponResource($this->specification),
            ],
            $this->type === 'WeaponMining' => [
                $this->specification->exists,
                new MiningLaserResource($this->specification),
            ],
            default => [false, []],
        };
    }

    private function addTurretData(): array
    {
        $mountName = 'max_mounts';
        if ($this->type === 'MissileLauncher') {
            $mountName = 'max_missiles';
        } elseif ($this->type === 'BombLauncher') {
            $mountName = 'max_bombs';
        }

        return [
            $mountName => $this->ports->count(),
            'min_size' => $this->ports->min('min_size'),
            'max_size' => $this->ports->max('max_size'),
        ];
    }
}
