<?php

declare(strict_types=1);

namespace App\Http\Resources\StarCitizen\Vehicle;

use App\Http\Resources\AbstractBaseResource;
use App\Http\Resources\SC\Shop\ShopResource;
use App\Http\Resources\SC\Vehicle\HardpointResource;
use App\Http\Resources\TranslationResourceFactory;
use App\Models\SC\Vehicle\Hardpoint;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'vehicle_v2',
    title: 'Vehicle',
    description: 'In-game or Ship-Matrix vehicles',
    properties: [
        new OA\Property(property: 'id', type: 'integer'),
        new OA\Property(property: 'uuid', type: 'string'),
        new OA\Property(property: 'chassis_id', type: 'integer'),
        new OA\Property(property: 'name', type: 'string'),
        new OA\Property(property: 'slug', type: 'string'),
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
        new OA\Property(property: 'cargo_capacity', type: 'float', nullable: true),
        new OA\Property(property: 'vehicle_inventory', type: 'float', nullable: true),
        new OA\Property(property: 'personal_inventory', type: 'float', nullable: true),
        new OA\Property(
            property: 'crew',
            properties: [
                new OA\Property(property: 'min', type: 'integer'),
                new OA\Property(property: 'max', type: 'integer'),
                new OA\Property(property: 'weapon', type: 'integer'),
                new OA\Property(property: 'operation', type: 'integer'),
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
                new OA\Property(property: 'afterburner', type: 'float'),
                new OA\Property(property: 'intake_rate', type: 'float'),
                new OA\Property(
                    property: 'usage',
                    properties: [
                        new OA\Property(property: 'main', type: 'float'),
                        new OA\Property(property: 'retro', type: 'float'),
                        new OA\Property(property: 'vtol', type: 'float'),
                        new OA\Property(property: 'maneuvering', type: 'float'),
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
            type: 'object'
        ),
        new OA\Property(
            property: 'agility',
            properties: [
                new OA\Property(property: 'pitch', type: 'float'),
                new OA\Property(property: 'yaw', type: 'float'),
                new OA\Property(property: 'roll', type: 'float'),
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
            property: 'components',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/vehicle_component_v2'),
        ),
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
            'components',
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

        $cargo = $this->sc?->cargo_capacity ?? $this->cargo_capacity;
        if ($this->sc?->SCU > 0) {
            $cargo = $this->sc?->scu;
        }
        $data = [
            'id' => $this->cig_id,
            'uuid' => $this->sc?->item_uuid,
            'chassis_id' => $this->chassis_id,
            'name' => $this->name,
            'slug' => $this->slug,
            'sizes' => [
                'length' => (double)$this->length,
                'beam' => (double)$this->width,
                'height' => (double)$this->height,
            ],
            'mass' => $this->sc?->mass ?? $this->mass,
            'cargo_capacity' => $cargo,
            'vehicle_inventory' => $this->sc?->vehicle_inventory_scu,
            'personal_inventory' => $this->sc?->personal_inventory_scu,

            'crew' => [
                'min' => $this->sc?->crew ?? $this->min_crew,
                'max' => $this->max_crew,
                'weapon' => $this->sc?->weapon_crew,
                'operation' => $this->sc?->operation_crew,
            ],
            'health' => $this->sc?->health,
            'speed' => [
                'scm' => $this->sc?->flightController()?->scm_speed ?? $this->scm_speed,
                'max' => $this->sc?->flightController()?->max_speed,
                'zero_to_scm' => $this->sc?->zero_to_scm,
                'zero_to_max' => $this->sc?->zero_to_max,
                'scm_to_zero' => $this->sc?->scm_to_zero,
                'max_to_zero' => $this->sc?->max_to_zero,
            ],
            'fuel' => [
                'capacity' => $this->sc?->fuel_capacity,
                'intake_rate' => $this->sc?->fuel_intake_rate,
                'usage' => [
                    'main' => $this->sc?->getFuelUsage(),
                    'maneuvering' => $this->sc?->getFuelUsage('ManneuverThruster'),
                    'retro' => $this->sc?->getFuelUsage('RetroThruster'),
                    'vtol' => $this->sc?->getFuelUsage('VtolThruster'),
                ],
            ],
            'quantum' => $this->getQuantumDriveData(),
            'agility' => [
                'pitch' => $this->sc?->flightController()?->pitch ?? $this->pitch_max,
                'yaw' => $this->sc?->flightController()?->yaw ?? $this->yaw_max,
                'roll' => $this->sc?->flightController()?->roll ?? $this->roll_max,
                'acceleration' => [
                    'x_axis' => $this->x_axis_acceleration,
                    'y_axis' => $this->y_axis_acceleration,
                    'z_axis' => $this->z_axis_acceleration,

                    'main' => $this->sc?->acceleration_main,
                    'retro' => $this->sc?->acceleration_retro,
                    'vtol' => $this->sc?->acceleration_vtol,
                    'maneuvering' => $this->sc?->acceleration_maneuvering,

                    'main_g' => $this->sc?->acceleration_g_main,
                    'retro_g' => $this->sc?->acceleration_g_retro,
                    'vtol_g' => $this->sc?->acceleration_g_vtol,
                    'maneuvering_g' => $this->sc?->acceleration_g_maneuvering,
                ],
            ],
            'foci' => $this->getFociTranslations($request),
            'production_status' => TranslationResourceFactory::getTranslationResource($request, $this->productionStatus),
            'production_note' => TranslationResourceFactory::getTranslationResource($request, $this->productionNote),
            'type' => TranslationResourceFactory::getTranslationResource($request, $this->type),
            'description' => TranslationResourceFactory::getTranslationResource($request, $this),
            'size' => TranslationResourceFactory::getTranslationResource($request, $this->size),
            'msrp' => $this->msrp,
            'manufacturer' => [
                'code' => $this->manufacturer->name_short,
                'name' => $this->manufacturer->name,
            ],
            'insurance' => [
                'claim_time' => $this->sc?->claim_time,
                'expedite_time' => $this->sc?->expedite_time,
                'expedite_cost' => $this->sc?->expedite_cost,
            ],
            $this->mergeWhen(in_array('components', $includes, true), [
                'components' => ComponentResource::collection($this->components),
            ]),
            $this->mergeWhen(in_array('hardpoints', $includes, true), [
                'hardpoints' => HardpointResource::collection($this->sc?->hardpointsWithoutParent),
            ]),
            $this->mergeWhen(in_array('shops', $includes, true) && $this->sc?->item?->shops !== null, [
                'shops' => ShopResource::collection($this->sc?->item?->shops ?? []),
            ]),
            'updated_at' => $this->updated_at,
        ];

        if ($this->sc?->crew !== null) {
            $data['version'] = config('api.sc_data_version');
        }

        return $data;
    }

    private function getFociTranslations(Request $request): array
    {
        /** @var Collection $foci */
        $foci = $this->foci;
        $fociTranslations = [];

        $foci->each(
            function ($vehicleFocus) use (&$fociTranslations, $request) {
                $fociTranslations[] = TranslationResourceFactory::getTranslationResource($request, $vehicleFocus);
            }
        );

        return $fociTranslations;
    }

    private function getQuantumDriveData(): array
    {
        $drives = $this->sc?->quantumDrives
            ->map(function (Hardpoint $hardpoint) {
                return $hardpoint->item->specification;
            });

        if ($drives->isEmpty()) {
            return [];
        }

        $modes = $drives[0]->modes->keyBy('type');
        $normal = $modes['normal'];

        return [
            'quantum_speed' => $normal->drive_speed,
            'quantum_spool_time' => $normal->spool_up_time,
            'quantum_fuel_capacity' => $this->sc?->quantum_fuel_capacity,
            'quantum_range' => $this->sc?->quantum_fuel_capacity / ($drives[0]->quantum_fuel_requirement / 1e6),
        ];
    }
}
