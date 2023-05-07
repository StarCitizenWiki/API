<?php

declare(strict_types=1);

namespace App\Http\Resources\StarCitizen\Vehicle;

use App\Http\Resources\AbstractBaseResource;
use App\Http\Resources\SC\HardpointResource;
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
        new OA\Property(property: 'cargo_capacity', type: 'float'),
        new OA\Property(property: 'personal_inventory_capacity', type: 'float'),
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
                new OA\Property(property: 'scm', type: 'float'),
                new OA\Property(property: 'afterburner', type: 'float'),
                new OA\Property(property: 'max', type: 'float'),
                new OA\Property(property: 'zero_to_scm', type: 'float'),
                new OA\Property(property: 'zero_to_max', type: 'float'),
                new OA\Property(property: 'scm_to_zero', type: 'float'),
                new OA\Property(property: 'max_to_zero', type: 'float'),
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
        new OA\Property(property: 'updated_at', type: 'timestamp'),
        new OA\Property(
            property: 'components',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/vehicle_component_v2'),
        ),
        new OA\Property(
            property: 'hardpoints',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/vehicle_hardpoint_v2'),
            nullable: true
        ),
        new OA\Property(
            property: 'shops',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/shop_v2'),
            nullable: true
        ),
    ],
    type: 'json'
)]
class VehicleResource extends AbstractBaseResource
{
    public static function validIncludes(): array
    {
        return [
            'components',
            'shops',
            'shops.items',
            'hardpoints',
            'hardpoints.item.heatData',
            'hardpoints.item.powerData',
            'hardpoints.item.distortionData',
            'hardpoints.item.durabilityData',
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
        $cargo = $this->sc->cargo_capacity ?? $this->cargo_capacity;
        if ($this->sc->SCU > 0) {
            $cargo = $this->sc->scu;
        }
        $data = [
            'id' => $this->cig_id,
            'uuid' => $this->sc->item_uuid,
            'chassis_id' => $this->chassis_id,
            'name' => $this->name,
            'slug' => $this->slug,
            'sizes' => [
                'length' => (double)$this->length,
                'beam' => (double)$this->width,
                'height' => (double)$this->height,
            ],
            'mass' => $this->sc->mass ?? $this->mass,
            'cargo_capacity' => $cargo,
            'personal_inventory' => $this->sc->personal_inventory_scu,

            'crew' => [
                'min' => $this->sc->crew ?? $this->min_crew,
                'max' => $this->max_crew,
                'weapon' => optional($this->sc)->weapon_crew,
                'operation' => optional($this->sc)->operation_crew,
            ],
            'health' => optional($this->sc)->health,
            'speed' => [
                'scm' => $this->sc->scm_speed ?? $this->scm_speed,
                'max' => optional($this->sc)->max_speed,
                'zero_to_scm' => optional($this->sc)->zero_to_scm,
                'zero_to_max' => optional($this->sc)->zero_to_max,
                'scm_to_zero' => optional($this->sc)->scm_to_zero,
                'max_to_zero' => optional($this->sc)->max_to_zero,
            ],
            'fuel' => [
                'capacity' => optional($this->sc)->fuel_capacity,
                'intake_rate' => optional($this->sc)->fuel_intake_rate,
                'usage' => [
                    'main' => optional($this->sc)->getFuelUsage(),
                    'maneuvering' => optional($this->sc)->getFuelUsage('ManneuverThruster'),
                ],
            ],
            'quantum' => $this->getQuantumDriveData(),
            'agility' => [
                'pitch' => $this->sc->flightController()->pitch ?? $this->pitch_max,
                'yaw' => $this->sc->flightController()->yaw ?? $this->yaw_max,
                'roll' => $this->sc->flightController()->roll ?? $this->roll_max,
                'acceleration' => [
                    'x_axis' => $this->x_axis_acceleration,
                    'y_axis' => $this->y_axis_acceleration,
                    'z_axis' => $this->z_axis_acceleration,

                    'main' => optional($this->sc)->acceleration_main,
                    'retro' => optional($this->sc)->acceleration_retro,
                    'vtol' => optional($this->sc)->acceleration_vtol,
                    'maneuvering' => optional($this->sc)->acceleration_maneuvering,

                    'main_g' => optional($this->sc)->acceleration_g_main,
                    'retro_g' => optional($this->sc)->acceleration_g_retro,
                    'vtol_g' => optional($this->sc)->acceleration_g_vtol,
                    'maneuvering_g' => optional($this->sc)->acceleration_g_maneuvering,
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
                'claim_time' => optional($this->sc)->claim_time,
                'expedite_time' => optional($this->sc)->expedite_time,
                'expedite_cost' => optional($this->sc)->expedite_cost,
            ],
            'components' => ComponentResource::collection($this->whenLoaded('components')),
            'hardpoints' => HardpointResource::collection($this->whenLoaded('hardpoints')),
            'updated_at' => $this->updated_at,
        ];

        if (optional($this->sc)->quantum_speed !== null) {
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

    private function getQuantumDriveData(): array {
        $drives = optional($this->sc)->quantumDrives
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
            'quantum_fuel_capacity' => optional($this->sc)->quantum_fuel_capacity,
            'quantum_range' => optional($this->sc)->quantum_fuel_capacity * $drives[0]->jump_range,
        ];
    }
}
