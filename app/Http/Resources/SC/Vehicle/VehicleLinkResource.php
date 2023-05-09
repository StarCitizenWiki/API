<?php

declare(strict_types=1);

namespace App\Http\Resources\SC\Vehicle;

use App\Http\Resources\AbstractBaseResource;
use App\Http\Resources\SC\HardpointResource;
use App\Http\Resources\StarCitizen\Vehicle\ComponentResource;
use App\Http\Resources\TranslationResourceFactory;
use App\Models\SC\Vehicle\Hardpoint;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
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
class VehicleLinkResource extends AbstractBaseResource
{
    public static function validIncludes(): array
    {
        return [
            'sc.item.shops',
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
        return [
            'uuid' => $this->item_uuid,
            'name' => $this->name,
            'link' => $this->makeApiUrl(self::VEHICLES_SHOW, $this->item_uuid),

            'updated_at' => $this->updated_at,
            'version' => config('api.sc_data_version'),
        ];
    }
}
