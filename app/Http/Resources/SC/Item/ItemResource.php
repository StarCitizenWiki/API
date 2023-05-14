<?php

declare(strict_types=1);

namespace App\Http\Resources\SC\Item;

use App\Http\Resources\AbstractTranslationResource;
use App\Http\Resources\SC\Char\ClothingResource;
use App\Http\Resources\SC\Char\PersonalWeapon\GrenadeResource;
use App\Http\Resources\SC\Char\PersonalWeapon\IronSightResource;
use App\Http\Resources\SC\Char\PersonalWeapon\PersonalWeaponMagazineResource;
use App\Http\Resources\SC\Char\PersonalWeapon\PersonalWeaponResource;
use App\Http\Resources\SC\FoodResource;
use App\Http\Resources\SC\ItemSpecification\CoolerResource;
use App\Http\Resources\SC\ItemSpecification\EmpResource;
use App\Http\Resources\SC\ItemSpecification\FlightControllerResource;
use App\Http\Resources\SC\ItemSpecification\FuelIntakeResource;
use App\Http\Resources\SC\ItemSpecification\FuelTankResource;
use App\Http\Resources\SC\ItemSpecification\MiningLaser\MiningLaserResource;
use App\Http\Resources\SC\ItemSpecification\MiningModuleResource;
use App\Http\Resources\SC\ItemSpecification\MissileResource;
use App\Http\Resources\SC\ItemSpecification\PowerPlantResource;
use App\Http\Resources\SC\ItemSpecification\QuantumDrive\QuantumDriveResource;
use App\Http\Resources\SC\ItemSpecification\QuantumInterdictionGeneratorResource;
use App\Http\Resources\SC\ItemSpecification\SelfDestructResource;
use App\Http\Resources\SC\ItemSpecification\ShieldResource;
use App\Http\Resources\SC\ItemSpecification\ThrusterResource;
use App\Http\Resources\SC\Manufacturer\ManufacturerLinkResource;
use App\Http\Resources\SC\Shop\ShopResource;
use App\Http\Resources\SC\Vehicle\VehicleWeaponResource;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'item_v2',
    title: 'Item',
    properties: [
        new OA\Property(property: 'uuid', type: 'string', nullable: true),
        new OA\Property(property: 'name', type: 'string', nullable: true),
        new OA\Property(
            property: 'description',
            oneOf: [
                new OA\Schema(type: 'string'),
                new OA\Schema(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/translation_v2'),
                ),
            ],
        ),
        new OA\Property(property: 'size', type: 'integer', nullable: true),
        new OA\Property(property: 'grade', type: 'string', nullable: true),
        new OA\Property(property: 'class', type: 'string', nullable: true),
        new OA\Property(property: 'manufacturer_description', type: 'string', nullable: true),
        new OA\Property(property: 'manufacturer', ref: '#/components/schemas/manufacturer_link_v2'),
        new OA\Property(property: 'type', type: 'string', nullable: true),
        new OA\Property(property: 'sub_type', type: 'string', nullable: true),
        new OA\Property(property: 'min_size', type: 'integer', nullable: true),
        new OA\Property(property: 'max_size', type: 'integer', nullable: true),
        new OA\Property(property: 'max_mounts', type: 'integer', nullable: true),
        new OA\Property(property: 'max_missiles', type: 'integer', nullable: true),
        new OA\Property(property: 'max_bombs', type: 'integer', nullable: true),
        new OA\Property(property: 'clothing', ref: '#/components/schemas/clothing_v2', nullable: true),
        new OA\Property(property: 'cooler', ref: '#/components/schemas/cooler_v2', nullable: true),
        new OA\Property(property: 'emp', ref: '#/components/schemas/emp_v2', nullable: true),
        new OA\Property(property: 'flight_controller', ref: '#/components/schemas/flight_controller_v2', nullable: true),
        new OA\Property(property: 'food', ref: '#/components/schemas/food_v2', nullable: true),
        new OA\Property(property: 'fuel_intake', ref: '#/components/schemas/fuel_intake_v2', nullable: true),
        new OA\Property(property: 'fuel_tank', ref: '#/components/schemas/fuel_tank_v2', nullable: true),
        new OA\Property(property: 'grenade', ref: '#/components/schemas/grenade_v2', nullable: true),
        new OA\Property(property: 'iron_sight', ref: '#/components/schemas/iron_sight_v2', nullable: true),
        new OA\Property(property: 'mining_laser', ref: '#/components/schemas/mining_laser_v2', nullable: true),
        new OA\Property(property: 'mining_module', ref: '#/components/schemas/mining_module_v2', nullable: true),
        new OA\Property(property: 'missile', ref: '#/components/schemas/missile_v2', nullable: true),
        new OA\Property(property: 'personal_weapon', ref: '#/components/schemas/personal_weapon_v2', nullable: true),
        new OA\Property(property: 'personal_weapon_magazine', ref: '#/components/schemas/personal_weapon_magazine_v2', nullable: true),
        new OA\Property(property: 'power_plant', ref: '#/components/schemas/power_plant_v2', nullable: true),
        new OA\Property(property: 'quantum_drive', ref: '#/components/schemas/quantum_drive_v2', nullable: true),
        new OA\Property(property: 'quantum_interdiction_generator', ref: '#/components/schemas/qig_v2', nullable: true),
        new OA\Property(property: 'self_destruct', ref: '#/components/schemas/self_destruct_v2', nullable: true),
        new OA\Property(property: 'shield', ref: '#/components/schemas/shield_v2', nullable: true),
        new OA\Property(property: 'thruster', ref: '#/components/schemas/thruster_v2', nullable: true),
        new OA\Property(property: 'vehicle_weapon', ref: '#/components/schemas/vehicle_weapon_v2', nullable: true),
        new OA\Property(property: 'dimension', ref: '#/components/schemas/item_dimension_v2'),
        new OA\Property(property: 'inventory', ref: '#/components/schemas/item_container_v2', nullable: true),
        new OA\Property(property: 'ports', ref: '#/components/schemas/item_port_data_v2', nullable: true),
        new OA\Property(property: 'heat', ref: '#/components/schemas/item_heat_data_v2', nullable: true),
        new OA\Property(property: 'power', ref: '#/components/schemas/item_power_data_v2', nullable: true),
        new OA\Property(property: 'distortion', ref: '#/components/schemas/item_distortion_data_v2', nullable: true),
        new OA\Property(property: 'durability', ref: '#/components/schemas/item_durability_data_v2', nullable: true),
        new OA\Property(property: 'shops', ref: '#/components/schemas/shop_v2', nullable: true),
        new OA\Property(property: 'updated_at', type: 'double', nullable: true),
        new OA\Property(
            property: 'version',
            description: 'The Game Version this item exists in.',
            type: 'double',
            nullable: true
        ),
    ],
    type: 'object'
)]
class ItemResource extends AbstractTranslationResource
{
    private bool $isVehicleItem;

    public function __construct($resource, bool $isVehicleItem = false)
    {
        parent::__construct($resource);
        $this->isVehicleItem = $isVehicleItem;
    }

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
            $this->mergeWhen($this->isVehicleItem || $this->vehicleItem->exists, [
                'grade' => $this->vehicleItem->grade,
                'class' => $this->vehicleItem->class,
            ]),
            'manufacturer_description' => $this->manufacturer_description,
            'manufacturer' => new ManufacturerLinkResource($this->manufacturer),
            'type' => $this->cleanType(),
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
            $this->mergeWhen(...$this->addBaseVersion()),
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
            $this->type === 'EMP' => [
                $this->specification->exists,
                ['emp' => new EmpResource($this->specification),],
            ],
            $this->type === 'Cooler' => [
                $this->specification->exists,
                ['cooler' => new CoolerResource($this->specification),],
            ],
            str_contains($this->type, 'Char_Clothing'), str_contains($this->type, 'Char_Armor') => [
                $this->specification->exists,
                ['clothing' => new ClothingResource($this->specification),],
            ],
            $this->type === 'Food', $this->type === 'Bottle', $this->type === 'Drink' => [
                $this->specification->exists,
                ['food' => new FoodResource($this->specification),],
            ],
            $this->type === 'MainThruster', $this->type === 'ManneuverThruster' => [
                $this->specification->exists,
                ['thruster' => new ThrusterResource($this->specification),],
            ],
            $this->type === 'PowerPlant' => [
                $this->specification->exists,
                ['power_plant' => new PowerPlantResource($this->specification),],
            ],
            $this->type === 'Shield' => [
                $this->specification->exists,
                ['shield' => new ShieldResource($this->specification),],
            ],
            $this->type === 'SelfDestruct' => [
                $this->specification->exists,
                ['self_destruct' => new SelfDestructResource($this->specification),],
            ],
            $this->type === 'FlightController' => [
                $this->specification->exists,
                ['flight_controller' => new FlightControllerResource($this->specification),],
            ],
            $this->type === 'FuelTank', $this->type === 'QuantumFuelTank' => [
                $this->specification->exists,
                ['fuel_tank' => new FuelTankResource($this->specification),],
            ],
            $this->type === 'FuelIntake' => [
                $this->specification->exists,
                ['fuel_intake' => new FuelIntakeResource($this->specification),],
            ],
            $this->type === 'QuantumInterdictionGenerator' => [
                $this->specification->exists,
                ['quantum_interdiction_generator' => new QuantumInterdictionGeneratorResource($this->specification),],
            ],
            $this->type === 'QuantumDrive' => [
                $this->specification->exists,
                ['quantum_drive' => new QuantumDriveResource($this->specification),],
            ],
            $this->type === 'WeaponPersonal' && $this->sub_type === 'Grenade' => [
                $this->specification->exists,
                ['grenade' => new GrenadeResource($this->specification),],
            ],
            $this->type === 'WeaponPersonal' => [
                $this->specification->exists,
                ['personal_weapon' => new PersonalWeaponResource($this->specification),],
            ],
            $this->sub_type === 'IronSight' => [
                $this->specification->exists,
                ['iron_sight' => new IronSightResource($this->specification),],
            ],
            $this->sub_type === 'Magazine' => [
                $this->specification->exists,
                ['personal_weapon_magazine' => new PersonalWeaponMagazineResource($this->specification),],
            ],
            $this->type === 'Missile', $this->type === 'Torpedo' => [
                $this->specification->exists,
                ['missile' => new MissileResource($this->specification),],
            ],
            $this->type === 'MiningModifier' => [
                $this->specification->exists,
                ['mining_module' => new MiningModuleResource($this->specification),],
            ],
            $this->type === 'WeaponGun', $this->type === 'WeaponDefensive' => [
                $this->specification->exists,
                [($this->type === 'WeaponGun' ?
                    'vehicle_weapon' :
                    'counter_measure') => new VehicleWeaponResource($this->specification),],
            ],
            $this->type === 'WeaponMining' => [
                $this->specification->exists,
                ['mining_laser' => new MiningLaserResource($this->specification),],
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

    private function addBaseVersion(): array {
        if ($this->specification === null || !is_callable([$this->specification, 'getBaseModelAttribute'])) {
            return [false, []];
        }

        return [
            true,
            [
                'base_version' => new ItemLinkResource($this->specification->base_model),
            ]
        ];
    }
}
