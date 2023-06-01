<?php

declare(strict_types=1);

namespace App\Http\Resources\SC\Vehicle;

use App\Http\Resources\AbstractBaseResource;
use App\Http\Resources\SC\ItemSpecification\ArmorResource;
use App\Http\Resources\SC\Shop\ShopResource;
use App\Http\Resources\TranslationResourceFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'sc_vehicle_v2',
    title: 'Vehicle',
    description: 'In-Game vehicles (with Ship-Matrix information if available)',
    properties: [
        new OA\Property(property: 'uuid', type: 'string'),
        new OA\Property(property: 'name', type: 'string'),
        new OA\Property(property: 'slug', type: 'string'),
        new OA\Property(property: 'class_name', type: 'string'),
        new OA\Property(
            property: 'sizes',
            properties: [
                new OA\Property(property: 'length', type: 'float'),
                new OA\Property(property: 'beam', type: 'float'),
                new OA\Property(property: 'height', type: 'float'),
            ],
            type: 'object'
        ),
        new OA\Property(
            property: 'emission',
            properties: [
                new OA\Property(
                    property: 'ir',
                    description: 'Infrared Emission',
                    type: 'float',
                    nullable: true
                ),
                new OA\Property(
                    property: 'em_idle',
                    description: 'Idle Elegromagnetic Emission',
                    type: 'float',
                    nullable: true
                ),
                new OA\Property(
                    property: 'em_max',
                    description: 'Maxmium possible Elegromagnetic Emission',
                    type: 'float',
                    nullable: true
                ),
            ],
            type: 'object'
        ),
        new OA\Property(property: 'mass', type: 'float'),
        new OA\Property(
            property: 'cargo_capacity',
            description: 'Cargo Capacity in SCU',
            type: 'float',
            nullable: true
        ),
        new OA\Property(
            property: 'vehicle_inventory',
            description: '(Personal) Vehicle Inventory (accessed via "I"), in SCU',
            type: 'float',
            nullable: true
        ),
        new OA\Property(
            property: 'personal_inventory',
            description: '(Shared) Inventories found in the ship, e.g. boxes or containers, in SCU',
            type: 'float',
            nullable: true
        ),
        new OA\Property(
            property: 'crew',
            properties: [
                new OA\Property(property: 'min', type: 'integer', nullable: true),
                new OA\Property(property: 'max', type: 'integer', nullable: true),
                new OA\Property(property: 'weapon', type: 'integer', nullable: true),
                new OA\Property(property: 'operation', type: 'integer', nullable: true),
            ],
            type: 'object'
        ),
        new OA\Property(property: 'health', type: 'float'),
        new OA\Property(property: 'shield_hp', type: 'float', nullable: true),
        new OA\Property(
            property: 'speed',
            properties: [
                new OA\Property(property: 'scm', type: 'float', nullable: true),
                new OA\Property(property: 'max', type: 'float', nullable: true),
                new OA\Property(property: 'reverse', description: 'Ground Vehicles', type: 'float', nullable: true),
                new OA\Property(property: 'zero_to_scm', type: 'float', nullable: true),
                new OA\Property(property: 'zero_to_max', type: 'float', nullable: true),
                new OA\Property(property: 'scm_to_zero', type: 'float', nullable: true),
                new OA\Property(property: 'max_to_zero', type: 'float', nullable: true),
            ],
            type: 'object'
        ),
        new OA\Property(
            property: 'fuel',
            properties: [
                new OA\Property(property: 'capacity', type: 'float'),
                new OA\Property(property: 'intake_rate', type: 'float'),
                new OA\Property(
                    property: 'usage',
                    properties: [
                        new OA\Property(property: 'main', type: 'float', nullable: true),
                        new OA\Property(property: 'retro', type: 'float', nullable: true),
                        new OA\Property(property: 'vtol', type: 'float', nullable: true),
                        new OA\Property(property: 'maneuvering', type: 'float', nullable: true),
                    ],
                    type: 'object'
                ),
            ],
            type: 'object'
        ),
        new OA\Property(
            property: 'quantum',
            properties: [
                new OA\Property(property: 'quantum_speed', type: 'float'),
                new OA\Property(property: 'quantum_spool_time', type: 'float'),
                new OA\Property(property: 'quantum_fuel_capacity', type: 'float'),
                new OA\Property(property: 'quantum_range', type: 'float'),
            ],
            type: 'object',
            nullable: true
        ),
        new OA\Property(
            property: 'agility',
            properties: [
                new OA\Property(property: 'pitch', type: 'float', nullable: true),
                new OA\Property(property: 'yaw', type: 'float', nullable: true),
                new OA\Property(property: 'roll', type: 'float', nullable: true),
                new OA\Property(property: 'v0_steer_max', description: 'Ground Vehicles', type: 'float', nullable: true),
                new OA\Property(property: 'kv_steer_max', description: 'Ground Vehicles', type: 'float', nullable: true),
                new OA\Property(property: 'vmax_steer_max', description: 'Ground Vehicles', type: 'float', nullable: true),
                new OA\Property(
                    property: 'acceleration',
                    properties: [
                        new OA\Property(property: 'x_axis', type: 'float'),
                        new OA\Property(property: 'y_axis', type: 'float'),
                        new OA\Property(property: 'z_axis', type: 'float'),

                        new OA\Property(property: 'main', type: 'float'),
                        new OA\Property(property: 'retro', type: 'float'),
                        new OA\Property(property: 'vtol', type: 'float'),
                        new OA\Property(property: 'maneuvering', type: 'float'),

                        new OA\Property(property: 'main_g', type: 'float'),
                        new OA\Property(property: 'retro_g', type: 'float'),
                        new OA\Property(property: 'vtol_g', type: 'float'),
                        new OA\Property(property: 'maneuvering_g', type: 'float'),
                    ],
                    type: 'object'
                ),
                new OA\Property(
                    property: 'deceleration',
                    description: 'Ground Vehicles',
                    properties: [
                        new OA\Property(property: 'main', type: 'float'),
                    ],
                    type: 'object',
                    nullable: true
                ),
            ],
            type: 'object'
        ),

        new OA\Property(property: 'armor', ref: '#/components/schemas/armor_v2', nullable: true),
        new OA\Property(
            property: 'foci',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/translation_v2')
        ),
        new OA\Property(property: 'production_status', ref: '#/components/schemas/translation_v2', nullable: true),
        new OA\Property(property: 'production_note', ref: '#/components/schemas/translation_v2', nullable: true),
        new OA\Property(property: 'type', ref: '#/components/schemas/translation_v2'),
        new OA\Property(property: 'description', ref: '#/components/schemas/translation_v2'),
        new OA\Property(property: 'size_class', type: 'integer'),
        new OA\Property(property: 'size', ref: '#/components/schemas/translation_v2', nullable: true),
        new OA\Property(
            property: 'msrp',
            description: 'MSRP imported from the Ship Upgrade tool.',
            type: 'float',
            nullable: true
        ),
        new OA\Property(
            property: 'manufacturer',
            properties: [
                new OA\Property(property: 'code', type: 'string'),
                new OA\Property(property: 'name', type: 'string'),
            ],
            type: 'object'
        ),
        new OA\Property(
            property: 'insurance',
            properties: [
                new OA\Property(property: 'claim_time', type: 'float'),
                new OA\Property(property: 'expedite_time', type: 'float'),
                new OA\Property(property: 'expedite_cost', type: 'float'),
            ],
            type: 'object'
        ),
        new OA\Property(property: 'updated_at', type: 'string'),
        new OA\Property(
            property: 'hardpoints',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/hardpoint_v2'),
            nullable: true
        ),
        new OA\Property(
            property: 'shops',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/shop_v2'),
            nullable: true
        ),
        new OA\Property(
            property: 'components',
            description: 'Components imported from the Ship-Matrix',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/vehicle_component_v2'),
            nullable: true
        ),
        new OA\Property(
            property: 'loaner',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/vehicle_loaner_v2'),
            nullable: true,
        ),
        new OA\Property(
            property: 'parts',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/vehicle_part_v2'),
            nullable: true,
        ),
    ],
    type: 'object'
)]
class VehicleResource extends AbstractBaseResource
{
    public static function validIncludes(): array
    {
        return [
            'shops',
            'hardpoints',
            'components',
        ];
    }

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        $includes = collect(explode(',', $request->get('include', '')))
            ->map('trim')
            ->map('strtolower')
            ->toArray();

        $manufacturer = $this->item->manufacturer->name;
        if ($manufacturer === 'Unknown Manufacturer') {
            $manufacturer = $this->description_manufacturer;
        }

        $data = [
            'uuid' => $this->item_uuid,
            'name' => $this->name,
            'slug' => Str::slug($this->name),
            'class_name' => $this->class_name,
            'sizes' => [
                'length' => (double)$this->length,
                'beam' => (double)$this->width,
                'height' => (double)$this->height,
            ],
            'emission' => [
                'ir' => $this->ir_emission,
                'em_idle' => $this->em_emission['min'] ?? null,
                'em_max' => $this->em_emission['max'] ?? null,
            ],
            'mass' => $this->mass,
            'cargo_capacity' => $this->scu,
            'vehicle_inventory' => $this->vehicle_inventory_scu,
            'personal_inventory' => $this->personal_inventory_scu,

            'crew' => [
                'min' => $this->crew,
                'max' => null,
                'weapon' => $this->weapon_crew,
                'operation' => $this->operation_crew,
            ],
            'health' => $this->health,
            'shield_hp' => $this->shield_hp,
            'speed' => [
                'scm' => $this->flightController?->scm_speed,
                'max' => $this->flightController?->max_speed ?? $this->handling?->max_speed,
                'zero_to_scm' => $this->zero_to_scm,
                'zero_to_max' => $this->handling?->zero_to_max ?? $this->zero_to_max,
                'scm_to_zero' => $this->scm_to_zero,
                'max_to_zero' => $this->handling?->max_to_zero ?? $this->max_to_zero,
                // Ground Vehicles
                $this->mergeWhen($this->handling !== null, [
                    'reverse' => $this->handling?->reverse_speed,
                ]),
            ],
            'fuel' => [
                'capacity' => $this->fuel_capacity,
                'intake_rate' => $this->fuel_intake_rate,
                'usage' => [
                    'main' => $this->getFuelUsage(),
                    'maneuvering' => $this->getFuelUsage('ManneuverThruster'),
                    'retro' => $this->getFuelUsage('RetroThruster'),
                    'vtol' => $this->getFuelUsage('VtolThruster'),
                ],
            ],
            $this->mergeWhen(...$this->getQuantumDriveData()),
            'agility' => [
                'pitch' => $this->flightController?->pitch,
                'yaw' => $this->flightController?->yaw,
                'roll' => $this->flightController?->roll,
                // Ground Vehicles
                $this->mergeWhen($this->handling !== null, [
                    'v0_steer_max' => $this->handling?->v0_steer_max,
                    'kv_steer_max' => $this->handling?->kv_steer_max,
                    'vmax_steer_max' => $this->handling?->vmax_steer_max,
                    'deceleration' => [
                        'main' => $this->handling?->deceleration,
                    ],
                ]),
                'acceleration' => [
                    'main' => $this->handling?->acceleration ?? $this->acceleration_main,
                    'retro' => $this->acceleration_retro,
                    'vtol' => $this->acceleration_vtol,
                    'maneuvering' => $this->acceleration_maneuvering,

                    'main_g' => $this->acceleration_g_main,
                    'retro_g' => $this->acceleration_g_retro,
                    'vtol_g' => $this->acceleration_g_vtol,
                    'maneuvering_g' => $this->acceleration_g_maneuvering,
                ],
            ],
            $this->mergeWhen($this->armor?->exists, [
                'armor' => new ArmorResource($this->armor),
            ]),
            'foci' => [
                'en_EN' => $this->career,
            ],
            'type' => [
                'en_EN' => $this->role,
            ],
            'description' => TranslationResourceFactory::getTranslationResource($request, $this),
            'size_class' => $this->size,
            'manufacturer' => [
                'name' => $manufacturer,
                'code' => $this->item->manufacturer->code,
            ],
            'insurance' => [
                'claim_time' => $this->claim_time,
                'expedite_time' => $this->expedite_time,
                'expedite_cost' => $this->expedite_cost,
            ],
            $this->mergeWhen(in_array('hardpoints', $includes, true), [
                'hardpoints' => HardpointResource::collection($this->hardpointsWithoutParent),
            ]),
            $this->mergeWhen(in_array('shops', $includes, true), [
                'shops' => ShopResource::collection($this->item->shops),
            ]),
            'parts' => VehiclePartResource::collection($this->whenLoaded('parts')),
            'updated_at' => $this->updated_at,
            'version' => $this->item->version,
        ];

        $this->loadShipMatrixData($data, $request);

        return $data;
    }

    private function getQuantumDriveData(): array
    {
        $drives = $this->quantumDrives;

        if ($drives->isEmpty()) {
            return [false, []];
        }

        $modes = $drives[0]->modes->keyBy('type');
        $normal = $modes['normal'];

        return [
            true,
            [
                'quantum' => [
                    'quantum_speed' => $normal->drive_speed,
                    'quantum_spool_time' => $normal->spool_up_time,
                    'quantum_fuel_capacity' => $this->quantum_fuel_capacity,
                    'quantum_range' => $this->quantum_fuel_capacity / ($drives[0]->quantum_fuel_requirement / 1e6),
                ]
            ]
        ];
    }

    /**
     * Adds ship-matrix information to the output
     *
     * @param array $data
     * @param Request $request
     * @return void
     */
    private function loadShipMatrixData(array &$data, Request $request): void
    {
        if (!$this->vehicle->exists) {
            return;
        }

        $matrixVehicle = (new \App\Http\Resources\StarCitizen\Vehicle\VehicleResource($this->vehicle))
            ->resolve($request);

        $toAdd = [
            'id',
            'chassis_id',
            'name',
            'slug',
            'foci',
            'production_status',
            'production_note',
            'type',
            'description',
            'size',
            'msrp',
            'components',
            'acceleration.x_axis',
            'acceleration.y_axis',
            'acceleration.z_axis',
            'loaner',
        ];

        foreach ($toAdd as $key) {
            if (!empty($matrixVehicle[$key])) {
                if (str_contains($key, 'acceleration')) {
                    $key = explode('.', $key)[1];
                    $data['acceleration'][$key] = $matrixVehicle['acceleration'][$key];
                } else {
                    $data[$key] = $matrixVehicle[$key];
                }
            }
        }
    }
}
