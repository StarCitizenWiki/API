<?php

declare(strict_types=1);

namespace App\Http\Resources\SC\Vehicle;

use App\Http\Resources\AbstractBaseResource;
use App\Http\Resources\SC\ItemSpecification\ArmorResource;
use App\Http\Resources\SC\Shop\ShopResource;
use App\Http\Resources\TranslationResourceFactory;
use App\Models\SC\Vehicle\Hardpoint;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'sc_vehicle_v2',
    title: 'Vehicle',
    description: 'In-game or Ship-Matrix vehicles',
    properties: [
        new OA\Property(property: 'uuid', type: 'string'),
        new OA\Property(property: 'name', type: 'string'),
        new OA\Property(property: 'slug', type: 'string'),
        new OA\Property(property: 'class_name', type: 'string', nullable: true),
        new OA\Property(
            property: 'sizes',
            properties: [
                new OA\Property(property: 'length', type: 'float'),
                new OA\Property(property: 'beam', type: 'float'),
                new OA\Property(property: 'height', type: 'float'),
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
        new OA\Property(
            property: 'speed',
            properties: [
                new OA\Property(property: 'scm', type: 'float', nullable: true),
                new OA\Property(property: 'max', type: 'float', nullable: true),
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
            ],
            type: 'object'
        ),

        new OA\Property(property: 'armor', ref: '#/components/schemas/armor_v2', nullable: true),
        new OA\Property(property: 'foci', type: 'object'),
        new OA\Property(property: 'production_status', type: 'object'),
        new OA\Property(property: 'production_note', type: 'object'),
        new OA\Property(property: 'type', type: 'object'),
        new OA\Property(property: 'description', type: 'object'),
        new OA\Property(property: 'size', type: 'object'),
        new OA\Property(property: 'msrp', type: 'float', nullable: true),
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
    ],
    type: 'object'
)]
class VehicleResource extends AbstractBaseResource
{
    public static function validIncludes(): array
    {
        return [
            'shops',
            'hardpoints'
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

        return [
            'uuid' => $this->item_uuid,
            'name' => $this->name,
            'slug' => Str::slug($this->name),
            'class_name' => $this->class_name,
            'sizes' => [
                'length' => (double)$this->length,
                'beam' => (double)$this->width,
                'height' => (double)$this->height,
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
            'speed' => [
                'scm' => $this->flightController?->scm_speed,
                'max' => $this->flightController?->max_speed,
                'zero_to_scm' => $this->zero_to_scm,
                'zero_to_max' => $this->zero_to_max,
                'scm_to_zero' => $this->scm_to_zero,
                'max_to_zero' => $this->max_to_zero,
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
            'quantum' => $this->getQuantumDriveData(),
            'agility' => [
                'pitch' => $this->flightController?->pitch,
                'yaw' => $this->flightController?->yaw,
                'roll' => $this->flightController?->roll,
                'acceleration' => [
                    'main' => $this->acceleration_main,
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
            'size' => $this->size,
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
            'updated_at' => $this->updated_at,
            'version' => config('api.sc_data_version'),
        ];
    }

    private function getQuantumDriveData(): array
    {
        $drives = $this->quantumDrives;

        if ($drives->isEmpty()) {
            return [];
        }

        $modes = $drives[0]->modes->keyBy('type');
        $normal = $modes['normal'];

        return [
            'quantum_speed' => $normal->drive_speed,
            'quantum_spool_time' => $normal->spool_up_time,
            'quantum_fuel_capacity' => $this->quantum_fuel_capacity,
            'quantum_range' => $this->sc?->quantum_fuel_capacity / ($drives[0]->quantum_fuel_requirement / 1e6),
        ];
    }
}
