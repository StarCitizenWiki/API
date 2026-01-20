<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Vehicle;

use App\Http\Resources\AbstractBaseResource;
use App\Http\Resources\Game\Concerns\ExtractsJsonData;
use App\Http\Resources\Game\Manufacturer\ManufacturerLinkResource;
use App\Http\Resources\StarCitizen\Vehicle\ComponentResource;
use App\Http\Resources\StarCitizen\Vehicle\VehicleLoanerResource;
use App\Http\Resources\StarCitizen\Vehicle\VehicleSkuResource;
use App\Models\Game\VehicleData;
use App\Traits\CalculatesCargoGridSizeLimits;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'game_vehicle',
    title: 'Game Vehicle',
    description: 'Vehicle data imported from scunpacked ship files for a specific game version.',
    properties: [
        new OA\Property(property: 'uuid', type: 'string', example: 'b5892cde-e805-4626-94a2-89fdf6eedc1c'),
        new OA\Property(property: 'name', type: 'string', example: 'Avenger Titan'),
        new OA\Property(property: 'game_name', type: 'string', example: 'AEGS_Avenger_Titan', nullable: true),
        new OA\Property(property: 'slug', type: 'string', example: 'avenger-titan'),
        new OA\Property(property: 'class_name', type: 'string', example: 'AEGS_Avenger_Titan'),
        new OA\Property(property: 'manufacturer', ref: '#/components/schemas/manufacturer_link'),
        new OA\Property(property: 'size_class', type: 'integer', example: 2, nullable: true),
        new OA\Property(
            property: 'dimension',
            properties: [
                new OA\Property(property: 'length', type: 'number', example: 18.0),
                new OA\Property(property: 'width', type: 'number', example: 16.0),
                new OA\Property(property: 'height', type: 'number', example: 5.0),
            ],
            type: 'object'
        ),
        new OA\Property(
            property: 'sizes',
            description: 'Deprecated, use dimension instead.',
            properties: [
                new OA\Property(property: 'length', type: 'number', example: 18.0),
                new OA\Property(property: 'beam', type: 'number', example: 16.0),
                new OA\Property(property: 'height', type: 'number', example: 5.0),
            ],
            type: 'object',
            deprecated: true
        ),
        new OA\Property(property: 'emission', description: 'Deprecated, use signature instead. Emission.ir currently maps to IR with shields active. Emission.em_max maps to EM Signature with quantum drive active. Emission.em_idle maps to EM Signature with shields active.', properties: [
            new OA\Property(property: 'ir', type: 'number', example: 4412, nullable: true),
            new OA\Property(property: 'em_idle', type: 'number', example: 14320, nullable: true),
            new OA\Property(property: 'em_max', type: 'number', example: 30458, nullable: true),
        ], type: 'object', nullable: true, deprecated: true),
        new OA\Property(property: 'mass', type: 'number', example: 53531.0, nullable: true, description: 'Deprecated, use mass_total instead. Mass is equal to mass_hull.'),
        new OA\Property(property: 'mass_hull', type: 'number', example: 53531.0, nullable: true),
        new OA\Property(property: 'mass_loadout', type: 'number', example: 0, nullable: true),
        new OA\Property(property: 'mass_total', type: 'number', example: 53531.0, nullable: true),
        new OA\Property(property: 'cargo_capacity', type: 'number', example: 8, nullable: true),
        new OA\Property(
            property: 'cargo_grids',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/game_vehicle_cargo_grid'),
            nullable: true
        ),
        new OA\Property(property: 'cargo_limits', description: 'Calculated cargo size limits based on grid dimensions.', properties: [
            new OA\Property(property: 'min', properties: [
                new OA\Property(property: 'x', type: 'number', example: 1.25, nullable: true),
                new OA\Property(property: 'y', type: 'number', example: 1.25, nullable: true),
                new OA\Property(property: 'z', type: 'number', example: 1.25, nullable: true),
            ], type: 'object', nullable: true),
            new OA\Property(property: 'max', properties: [
                new OA\Property(property: 'x', type: 'number', example: 2.5, nullable: true),
                new OA\Property(property: 'y', type: 'number', example: 2.5, nullable: true),
                new OA\Property(property: 'z', type: 'number', example: 1.25, nullable: true),
            ], type: 'object', nullable: true),
        ], type: 'object', nullable: true),
        new OA\Property(property: 'vehicle_inventory', type: 'number', example: 0, nullable: true),
        new OA\Property(property: 'inventory_containers', type: 'object', example: ['Container' => []], nullable: true),
        new OA\Property(
            property: 'crew',
            properties: [
                new OA\Property(property: 'min', type: 'integer', example: 1, nullable: true),
                new OA\Property(property: 'max', type: 'integer', example: 1, nullable: true),
                new OA\Property(property: 'weapon', type: 'integer', example: 1, nullable: true),
                new OA\Property(property: 'operation', type: 'integer', nullable: true),
            ],
            type: 'object',
            nullable: true
        ),
        new OA\Property(property: 'is_vehicle', type: 'boolean', example: false, nullable: true),
        new OA\Property(property: 'is_gravlev', type: 'boolean', example: false, nullable: true),
        new OA\Property(property: 'is_spaceship', type: 'boolean', example: true, nullable: true),
        new OA\Property(property: 'health', type: 'number', example: 2500, nullable: true),
        new OA\Property(property: 'shield_hp', type: 'number', example: 12000, nullable: true, deprecated: true, description: 'Use shield.hp property instead.'),
        new OA\Property(property: 'shield_face_type', type: 'string', example: 'FourFaces', nullable: true, deprecated: true, description: 'Use shield.face_type property instead.'),
        new OA\Property(
            property: 'shield',
            properties: [
                new OA\Property(property: 'hp', type: 'number', example: 12000, nullable: true),
                new OA\Property(property: 'regeneration', type: 'number', example: 50, nullable: true),
                new OA\Property(property: 'face_type', type: 'string', example: 'FourFaces', nullable: true),
                new OA\Property(property: 'max_reallocation', type: 'number', example: 0.5, nullable: true),
                new OA\Property(property: 'reconfiguration_cooldown', type: 'number', example: 2.0, nullable: true),
                new OA\Property(property: 'max_electrical_charge_damage_rate', type: 'number', example: 100, nullable: true),
            ],
            type: 'object',
            nullable: true
        ),
        new OA\Property(
            property: 'speed',
            properties: [
                new OA\Property(property: 'scm', type: 'number', example: 260),
                new OA\Property(property: 'max', type: 'number', example: 1425),
                new OA\Property(property: 'boost_forward', type: 'number', example: 610, nullable: true),
                new OA\Property(property: 'boost_backward', type: 'number', example: 280, nullable: true),
                new OA\Property(property: 'zero_to_scm', type: 'number', example: 2.04, nullable: true),
                new OA\Property(property: 'zero_to_max', type: 'number', example: 11.19, nullable: true),
                new OA\Property(property: 'scm_to_zero', type: 'number', example: 6368.27, nullable: true),
                new OA\Property(property: 'max_to_zero', type: 'number', example: 34903.01, nullable: true),
            ],
            type: 'object',
            nullable: true
        ),
        new OA\Property(
            property: 'agility',
            properties: [
                new OA\Property(property: 'pitch', type: 'number', example: 52.5, nullable: true),
                new OA\Property(property: 'yaw', type: 'number', example: 48.5, nullable: true),
                new OA\Property(property: 'roll', type: 'number', example: 180, nullable: true),
                new OA\Property(property: 'pitch_boosted', type: 'number', example: 63, nullable: true),
                new OA\Property(property: 'yaw_boosted', type: 'number', example: 58.2, nullable: true),
                new OA\Property(property: 'roll_boosted', type: 'number', example: 216, nullable: true),
                new OA\Property(property: 'acceleration', properties: [
                    new OA\Property(property: 'main', type: 'number', example: 127.339, nullable: true),
                    new OA\Property(property: 'retro', type: 'number', example: 0.041, nullable: true),
                    new OA\Property(property: 'vtol', type: 'number', example: 0, nullable: true),
                    new OA\Property(property: 'maneuvering', type: 'number', example: 121.49, nullable: true),
                    new OA\Property(property: 'main_g', type: 'number', example: 12.985, nullable: true),
                    new OA\Property(property: 'retro_g', type: 'number', example: 0.004, nullable: true),
                    new OA\Property(property: 'vtol_g', type: 'number', example: 0, nullable: true),
                    new OA\Property(property: 'maneuvering_g', type: 'number', example: 12.389, nullable: true),
                ], type: 'object'),
            ],
            type: 'object',
            nullable: true
        ),
        new OA\Property(
            property: 'afterburner',
            description: 'Afterburner properties from FlightCharacteristics.Afterburner.',
            properties: [
                new OA\Property(property: 'pitch_boost_multiplier', type: 'number', example: 1.2, nullable: true),
                new OA\Property(property: 'roll_boost_multiplier', type: 'number', example: 1.2, nullable: true),
                new OA\Property(property: 'yaw_boost_multiplier', type: 'number', example: 1.2, nullable: true),
                new OA\Property(property: 'capacitor', type: 'number', example: 20, nullable: true),
                new OA\Property(property: 'idle_cost', type: 'number', example: 1, nullable: true),
                new OA\Property(property: 'linear_cost', type: 'number', example: 0, nullable: true),
                new OA\Property(property: 'angular_cost', type: 'number', example: 0, nullable: true),
                new OA\Property(property: 'regen_per_second', type: 'number', example: 0.75, nullable: true),
                new OA\Property(property: 'regen_delay_after_use', type: 'number', example: 0.2, nullable: true),
                new OA\Property(property: 'pre_delay_time', type: 'number', example: 0, nullable: true),
                new OA\Property(property: 'ramp_up_time', type: 'number', example: 0.4, nullable: true),
                new OA\Property(property: 'ramp_down_time', type: 'number', example: 0.2, nullable: true),
            ],
            type: 'object',
            nullable: true
        ),
        new OA\Property(
            property: 'fuel',
            properties: [
                new OA\Property(property: 'capacity', type: 'number', example: 9),
                new OA\Property(property: 'intake_rate', type: 'number', example: 22, nullable: true),
                new OA\Property(
                    property: 'usage',
                    properties: [
                        new OA\Property(property: 'main', type: 'number', example: 40.1177, nullable: true),
                        new OA\Property(property: 'retro', type: 'number', example: 0.0128, nullable: true),
                        new OA\Property(property: 'vtol', type: 'number', example: 0, nullable: true),
                        new OA\Property(property: 'maneuvering', type: 'number', example: 18.5981, nullable: true),
                    ],
                    type: 'object',
                    nullable: true
                ),
            ],
            type: 'object',
            nullable: true
        ),
        new OA\Property(
            property: 'quantum',
            properties: [
                new OA\Property(property: 'quantum_speed', type: 'number', example: 165000000, nullable: true),
                new OA\Property(property: 'quantum_spool_time', type: 'number', example: 4, nullable: true),
                new OA\Property(property: 'quantum_fuel_capacity', type: 'number', example: 1.1, nullable: true),
                new OA\Property(property: 'quantum_range', type: 'number', example: 112244897.9592, nullable: true),
                new OA\Property(property: 'port_olisar_to_arccorp_time', type: 'number', example: 254.105158, nullable: true),
                new OA\Property(property: 'port_olisar_to_arccorp_fuel', type: 'number', example: 410.888040486, nullable: true),
            ],
            type: 'object',
            nullable: true
        ),
        new OA\Property(
            property: 'armor',
            properties: [
                new OA\Property(property: 'uuid', type: 'string', example: 'armor-uuid', nullable: true),
                new OA\Property(property: 'health', type: 'number', example: 1000, nullable: true),
                new OA\Property(property: 'signal_infrared', type: 'number', example: 1, nullable: true),
                new OA\Property(property: 'signal_electromagnetic', type: 'number', example: 1, nullable: true),
                new OA\Property(property: 'signal_cross_section', type: 'number', example: 1, nullable: true),
                new OA\Property(property: 'damage_physical', type: 'number', example: 0.62, nullable: true),
                new OA\Property(property: 'damage_energy', type: 'number', example: 1, nullable: true),
                new OA\Property(property: 'damage_distortion', type: 'number', example: 1, nullable: true),
                new OA\Property(property: 'damage_thermal', type: 'number', example: 1, nullable: true),
                new OA\Property(property: 'damage_biochemical', type: 'number', example: 1, nullable: true),
                new OA\Property(property: 'damage_stun', type: 'number', example: 0, nullable: true),
                new OA\Property(property: 'signal_multipliers', properties: [
                    new OA\Property(property: 'cross_section', type: 'number', example: 1, nullable: true),
                    new OA\Property(property: 'infrared', type: 'number', example: 1, nullable: true),
                    new OA\Property(property: 'electromagnetic', type: 'number', example: 1, nullable: true),
                ], type: 'object', nullable: true),
                new OA\Property(property: 'damage_multipliers', properties: [
                    new OA\Property(property: 'physical', type: 'number', example: 0.62, nullable: true),
                    new OA\Property(property: 'energy', type: 'number', example: 1, nullable: true),
                    new OA\Property(property: 'distortion', type: 'number', example: 1, nullable: true),
                    new OA\Property(property: 'thermal', type: 'number', example: 1, nullable: true),
                    new OA\Property(property: 'biochemical', type: 'number', example: 1, nullable: true),
                    new OA\Property(property: 'stun', type: 'number', example: 0, nullable: true),
                ], type: 'object', nullable: true),
                new OA\Property(property: 'resistance_multipliers', properties: [
                    new OA\Property(property: 'physical', type: 'number', example: 0.001, nullable: true),
                    new OA\Property(property: 'energy', type: 'number', example: 0.001, nullable: true),
                    new OA\Property(property: 'distortion', type: 'number', example: 0.001, nullable: true),
                    new OA\Property(property: 'thermal', type: 'number', example: 0.001, nullable: true),
                    new OA\Property(property: 'biochemical', type: 'number', example: 0.001, nullable: true),
                    new OA\Property(property: 'stun', type: 'number', example: 0.001, nullable: true),
                ], type: 'object', nullable: true),
                new OA\Property(property: 'penetration_resistance', properties: [
                    new OA\Property(property: 'base', type: 'number', example: 0.001, nullable: true),
                    new OA\Property(property: 'physical', type: 'number', example: 0.001, nullable: true),
                    new OA\Property(property: 'energy', type: 'number', example: 0.001, nullable: true),
                    new OA\Property(property: 'distortion', type: 'number', example: 0.001, nullable: true),
                    new OA\Property(property: 'thermal', type: 'number', example: 0.001, nullable: true),
                    new OA\Property(property: 'biochemical', type: 'number', example: 0.001, nullable: true),
                    new OA\Property(property: 'stun', type: 'number', example: 0.001, nullable: true),
                ], type: 'object', nullable: true),
            ],
            type: 'object',
            nullable: true
        ),
        new OA\Property(
            property: 'insurance',
            properties: [
                new OA\Property(property: 'claim_time', type: 'number', example: 4.05, nullable: true),
                new OA\Property(property: 'expedite_time', type: 'number', example: 1.35, nullable: true),
                new OA\Property(property: 'expedite_cost', type: 'number', example: 2343, nullable: true),
            ],
            type: 'object',
            nullable: true
        ),
        new OA\Property(
            property: 'damage_limits',
            properties: [
                new OA\Property(
                    property: 'before_destruction',
                    description: 'Map of part name => damage capacity before destruction.',
                    type: 'object',
                    example: ['Nose' => 2500, 'Body' => 2500],
                    nullable: true
                ),
                new OA\Property(
                    property: 'before_detach',
                    description: 'Map of part name => damage before detachment.',
                    type: 'object',
                    example: ['WingTipLeft' => 1000, 'WingTipRight' => 1000],
                    nullable: true
                ),
            ],
            type: 'object',
            nullable: true
        ),
        new OA\Property(
            property: 'ports',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/game_vehicle_port'),
            nullable: true
        ),
        new OA\Property(
            property: 'parts',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/game_vehicle_part'),
            nullable: true
        ),
        new OA\Property(
            property: 'turrets',
            properties: [
                new OA\Property(property: 'manned', type: 'array', items: new OA\Items(ref: '#/components/schemas/game_vehicle_turret'), nullable: true),
                new OA\Property(property: 'remote', type: 'array', items: new OA\Items(ref: '#/components/schemas/game_vehicle_turret'), nullable: true),
            ],
            type: 'object',
            nullable: true
        ),
        new OA\Property(
            property: 'cross_section',
            description: 'Vehicle cross-section dimensions',
            properties: [
                new OA\Property(property: 'length', type: 'number', example: 18.0, nullable: true),
                new OA\Property(property: 'width', type: 'number', example: 16.0, nullable: true),
                new OA\Property(property: 'height', type: 'number', example: 5.0, nullable: true),
            ],
            type: 'object',
            nullable: true
        ),
        new OA\Property(property: 'is_vehicle', type: 'boolean', example: false, nullable: true),
        new OA\Property(property: 'is_gravlev', type: 'boolean', example: false, nullable: true),
        new OA\Property(property: 'is_spaceship', type: 'boolean', example: true, nullable: true),
        new OA\Property(
            property: 'signature',
            description: 'EM and IR signature data',
            properties: [
                new OA\Property(property: 'ir_quantum', type: 'number', example: 4412, nullable: true),
                new OA\Property(property: 'ir_shields', type: 'number', example: 14320, nullable: true),
                new OA\Property(property: 'em_quantum', type: 'number', example: 30458, nullable: true),
                new OA\Property(property: 'em_shields', type: 'number', example: 14320, nullable: true),
                new OA\Property(property: 'em_groups_quantum', type: 'array', items: new OA\Items(type: 'number'), nullable: true),
                new OA\Property(property: 'em_groups_shields', type: 'array', items: new OA\Items(type: 'number'), nullable: true),
                new OA\Property(property: 'em_segment_groups_quantum', type: 'array', items: new OA\Items(type: 'number'), nullable: true),
                new OA\Property(property: 'em_segment_groups_shields', type: 'array', items: new OA\Items(type: 'number'), nullable: true),
                new OA\Property(property: 'em_per_segment', type: 'number', nullable: true),
            ],
            type: 'object',
            nullable: true
        ),
        new OA\Property(
            property: 'cooling',
            description: 'Cooling system data',
            properties: [
                new OA\Property(property: 'generation_segments', type: 'number', nullable: true),
                new OA\Property(property: 'usage_shields_pct', type: 'number', nullable: true),
                new OA\Property(property: 'usage_quantum_pct', type: 'number', nullable: true),
                new OA\Property(property: 'used_segments_shields', type: 'number', nullable: true),
                new OA\Property(property: 'used_segments_quantum', type: 'number', nullable: true),
                new OA\Property(property: 'used_segments_shields_grouped', type: 'array', items: new OA\Items(type: 'number'), nullable: true),
                new OA\Property(property: 'used_segments_quantum_grouped', type: 'array', items: new OA\Items(type: 'number'), nullable: true),
            ],
            type: 'object',
            nullable: true
        ),
        new OA\Property(
            property: 'power',
            description: 'Power system data',
            properties: [
                new OA\Property(property: 'generation_segments', type: 'number', nullable: true),
                new OA\Property(property: 'used_segments_shields', type: 'number', nullable: true),
                new OA\Property(property: 'used_segments_quantum', type: 'number', nullable: true),
                new OA\Property(property: 'used_segments_grouped', type: 'array', items: new OA\Items(type: 'number'), nullable: true),
            ],
            type: 'object',
            nullable: true
        ),
        new OA\Property(
            property: 'penetration_multiplier',
            description: 'Penetration multiplier data',
            properties: [
                new OA\Property(property: 'fuse', type: 'number', nullable: true),
                new OA\Property(property: 'components', type: 'number', nullable: true),
            ],
            type: 'object',
            nullable: true
        ),
        new OA\Property(property: 'career', type: 'string', example: 'Light Freight', nullable: true),
        new OA\Property(property: 'role', type: 'string', example: 'Combat', nullable: true),
        new OA\Property(property: 'web_url', type: 'string', example: 'https://example.com/vehicles/uuid', nullable: true),
        new OA\Property(property: 'link', type: 'string', example: 'https://api.example.com/vehicles/uuid'),
        new OA\Property(property: 'description', type: 'string', nullable: true),
        new OA\Property(
            property: 'id',
            description: 'Ship-Matrix CIG ID',
            type: 'integer',
            nullable: true
        ),
        new OA\Property(
            property: 'chassis_id',
            description: 'Ship-Matrix chassis ID',
            type: 'integer',
            nullable: true
        ),
        new OA\Property(
            property: 'shipmatrix_name',
            description: 'Ship-Matrix vehicle name',
            type: 'string',
            nullable: true
        ),
        new OA\Property(
            property: 'foci',
            description: 'Ship-Matrix vehicle foci/roles',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/translation'),
            nullable: true
        ),
        new OA\Property(
            property: 'production_status',
            ref: '#/components/schemas/translation',
            description: 'Ship-Matrix production status',
            nullable: true
        ),
        new OA\Property(
            property: 'production_note',
            ref: '#/components/schemas/translation',
            description: 'Ship-Matrix production note',
            nullable: true
        ),
        new OA\Property(
            property: 'type',
            ref: '#/components/schemas/translation',
            description: 'Ship-Matrix vehicle type',
            nullable: true
        ),
        new OA\Property(
            property: 'shipmatrix_description',
            ref: '#/components/schemas/translation',
            description: 'Ship-Matrix vehicle description',
            nullable: true
        ),
        new OA\Property(
            property: 'msrp',
            description: 'Ship-Matrix MSRP in USD',
            type: 'number',
            nullable: true
        ),
        new OA\Property(
            property: 'pledge_url',
            description: 'Link to RSI pledge store',
            type: 'string',
            nullable: true
        ),
        new OA\Property(
            property: 'loaner',
            description: 'Ship-Matrix loaner vehicles',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/vehicle_loaner'),
            nullable: true
        ),
        new OA\Property(
            property: 'skus',
            description: 'Ship-Matrix SKUs',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/vehicle_sku'),
            nullable: true
        ),
        new OA\Property(
            property: 'components',
            description: 'Ship-Matrix components (only included when ?include=components)',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/vehicle_component'),
            nullable: true
        ),
        new OA\Property(property: 'updated_at', type: 'string'),
        new OA\Property(property: 'version', type: 'string', example: '4.4.0-LIVE.12340123'),
    ],
    type: 'object'
)]
class VehicleResource extends AbstractBaseResource
{
    use CalculatesCargoGridSizeLimits;
    use ExtractsJsonData;

    public static function validIncludes(): array
    {
        return [
            'ports',
            'hardpoints',
            'components',
        ];
    }

    public function toArray(Request $request): array
    {
        $vehicleData = $this->data->first();

        if ($vehicleData === null) {
            return [];
        }

        $payload = $vehicleData->data ?? [];
        $flight = Arr::get($payload, 'FlightCharacteristics', []);

        $apiVersion = $this->getApiVersion($request);

        $hardpoints = $apiVersion === 'v2'
            ? HardpointResource::collection(Arr::get($payload, 'Loadout', []))
            : PortResource::collection(Arr::get($payload, 'Loadout', []));

        $portKey = $apiVersion === 'v2' ? 'hardpoints' : 'ports';

        $cargoGridPayload = Arr::get($payload, 'CargoGrids', []);
        $cargoGrids = CargoGridResource::collection($cargoGridPayload);
        $cargoLimits = self::calculateCargoGridSizeLimits($cargoGridPayload);

        $this->addMetadata('deprecated_fields', [
            'sizes' => 'Use length, width, and height properties from dimension instead',
            'emission' => 'Use properties from signature instead. Emission.ir currently maps to IR with shields active. Emission.em_max maps to EM Signature with quantum drive active. Emission.em_idle maps to EM Signature with shields active.',
            'mass' => 'Use mass_total property instead. Mass is equal to mass_hull.',
            'shield_hp' => 'Use shield.hp property instead.',
            'shield_face_type' => 'Use shield.face_type property instead.',

            'personal_inventory' => 'No replacement.',

            "{$portKey}[].equipped_item.$portKey" => "Use {$portKey}[].$portKey instead.",
        ]);

        $data = [
            'uuid' => $this->uuid,
            'name' => $vehicleData->display_name ?? $vehicleData->name,
            'game_name' => $vehicleData->name,
            'slug' => Str::slug($vehicleData->display_name ?? $vehicleData->name),
            'class_name' => $vehicleData->class_name,

            'sizes' => [
                'length' => $vehicleData->length ?? Arr::get($payload, 'Length'),
                'beam' => $vehicleData->width ?? Arr::get($payload, 'Width'),
                'height' => $vehicleData->height ?? Arr::get($payload, 'Height'),
            ],
            'dimension' => [
                'length' => Arr::get($payload, 'Length'),
                'width' => Arr::get($payload, 'Width'),
                'height' => Arr::get($payload, 'Height'),
            ],
            'emission' => [
                'ir' => Arr::get($payload, 'Emission.IrShields'),
                'em_idle' => Arr::get($payload, 'Emission.EmShields'),
                'em_max' => Arr::get($payload, 'Emission.EmQuantum'),
            ],

            'mass' => $vehicleData->mass ?? Arr::get($payload, 'Mass'),

            'mass_hull' => $vehicleData->mass_vehicle ?? Arr::get($payload, 'Mass'),
            'mass_loadout' => $vehicleData->mass_loadout ?? Arr::get($payload, 'MassLoadout'),
            'mass_total' => $vehicleData->mass_total ?? Arr::get($payload, 'MassTotal'),

            'cargo_capacity' => $vehicleData->cargo ?? Arr::get($payload, 'Cargo'),
            'cargo_grids' => $cargoGrids,
            $this->mergeWhen(
                ! empty($cargoLimits),
                fn () => ['cargo_limits' => $cargoLimits]
            ),
            'vehicle_inventory' => Arr::get($payload, 'Stowage', 0),
            'inventory_containers' => Arr::get($payload, 'InventoryContainers'),

            'crew' => [
                'min' => Arr::get($payload, 'Crew'),
                'max' => Arr::get($payload, 'Crew'), // TODO
                'weapon' => Arr::get($payload, 'WeaponCrew'),
                'operation' => null, // TODO
            ],

            'health' => Arr::get($payload, 'Health', 0),

            'shield_hp' => Arr::get($payload, 'ShieldsTotal.Hp'),
            'shield_face_type' => Arr::get($payload, 'ShieldController.FaceType'),

            'shield' => [
                'hp' => Arr::get($payload, 'ShieldsTotal.Hp', 0),
                'regeneration' => Arr::get($payload, 'ShieldsTotal.Regen'),
                'face_type' => Arr::get($payload, 'ShieldController.FaceType'),
                'max_reallocation' => Arr::get($payload, 'ShieldController.MaxReallocation'),
                'reconfiguration_cooldown' => Arr::get($payload, 'ShieldController.ReconfigurationCooldown'),
                'max_electrical_charge_damage_rate' => Arr::get($payload, 'ShieldController.MaxElectricalChargeDamageRate'),
            ],

            'speed' => $this->buildSpeed($flight),

            'afterburner' => [
                'pitch_boost_multiplier' => Arr::get($flight, 'Afterburner.AngularAccelerationMultiplier.Pitch'),
                'roll_boost_multiplier' => Arr::get($flight, 'Afterburner.AngularAccelerationMultiplier.Roll'),
                'yaw_boost_multiplier' => Arr::get($flight, 'Afterburner.AngularAccelerationMultiplier.Yaw'),

                'capacitor' => Arr::get($flight, 'Afterburner.CapacitorMax'),
                'idle_cost' => Arr::get($flight, 'Afterburner.CapacitorAfterburnerIdleCost'),
                'linear_cost' => Arr::get($flight, 'Afterburner.CapacitorAfterburnerLinearCost'),
                'angular_cost' => Arr::get($flight, 'Afterburner.CapacitorAfterburnerAngularCost'),
                'regen_per_second' => Arr::get($flight, 'Afterburner.CapacitorRegenPerSec'),
                'regen_delay_after_use' => Arr::get($flight, 'Afterburner.CapacitorRegenDelayAfterUse'),
                'pre_delay_time' => Arr::get($flight, 'Afterburner.AfterburnerPreDelayTime'),
                'ramp_up_time' => Arr::get($flight, 'Afterburner.AfterburnerRampUpTime'),
                'ramp_down_time' => Arr::get($flight, 'Afterburner.AfterburnerRampDownTime'),
            ],

            'fuel' => $this->buildFuel($payload),
            'quantum' => $this->buildQuantum($payload),

            // TODO
            'agility' => $this->buildAgility($flight),

            'armor' => [
                'uuid' => Arr::get($payload, 'Armor.UUID'),
                'health' => Arr::get($payload, 'Armor.Health'),

                'signal_infrared' => Arr::get($payload, 'Armor.SignalMultipliers.Infrared'),
                'signal_electromagnetic' => Arr::get($payload, 'Armor.SignalMultipliers.Electromagnetic'),
                'signal_cross_section' => Arr::get($payload, 'Armor.SignalMultipliers.CrossSection'),

                'damage_physical' => Arr::get($payload, 'Armor.DamageMultipliers.Physical'),
                'damage_energy' => Arr::get($payload, 'Armor.DamageMultipliers.Energy'),
                'damage_distortion' => Arr::get($payload, 'Armor.DamageMultipliers.Distortion'),
                'damage_thermal' => Arr::get($payload, 'Armor.DamageMultipliers.Thermal'),
                'damage_biochemical' => Arr::get($payload, 'Armor.DamageMultipliers.Biochemical'),
                'damage_stun' => Arr::get($payload, 'Armor.DamageMultipliers.Stun'),

                'signal_multipliers' => [
                    'cross_section' => Arr::get($payload, 'Armor.SignalMultipliers.CrossSection'),
                    'infrared' => Arr::get($payload, 'Armor.SignalMultipliers.Infrared'),
                    'electromagnetic' => Arr::get($payload, 'Armor.SignalMultipliers.Electromagnetic'),
                ],
                'damage_multipliers' => [
                    'physical' => Arr::get($payload, 'Armor.DamageMultipliers.Physical'),
                    'energy' => Arr::get($payload, 'Armor.DamageMultipliers.Energy'),
                    'distortion' => Arr::get($payload, 'Armor.DamageMultipliers.Distortion'),
                    'thermal' => Arr::get($payload, 'Armor.DamageMultipliers.Thermal'),
                    'biochemical' => Arr::get($payload, 'Armor.DamageMultipliers.Biochemical'),
                    'stun' => Arr::get($payload, 'Armor.DamageMultipliers.Stun'),
                ],
                'resistance_multipliers' => [
                    'physical' => Arr::get($payload, 'Armor.ResistanceMultiplier.Physical'),
                    'energy' => Arr::get($payload, 'Armor.ResistanceMultiplier.Energy'),
                    'distortion' => Arr::get($payload, 'Armor.ResistanceMultiplier.Distortion'),
                    'thermal' => Arr::get($payload, 'Armor.ResistanceMultiplier.Thermal'),
                    'biochemical' => Arr::get($payload, 'Armor.ResistanceMultiplier.Biochemical'),
                    'stun' => Arr::get($payload, 'Armor.ResistanceMultiplier.Stun'),
                ],
                'penetration_resistance' => [
                    'base' => Arr::get($payload, 'Armor.PenetrationResistance.Base'),
                    'physical' => Arr::get($payload, 'Armor.PenetrationResistance.Physical'),
                    'energy' => Arr::get($payload, 'Armor.PenetrationResistance.Energy'),
                    'distortion' => Arr::get($payload, 'Armor.PenetrationResistance.Distortion'),
                    'thermal' => Arr::get($payload, 'Armor.PenetrationResistance.Thermal'),
                    'biochemical' => Arr::get($payload, 'Armor.PenetrationResistance.Biochemical'),
                    'stun' => Arr::get($payload, 'Armor.PenetrationResistance.Stun'),
                ],
            ],

            'manufacturer' => new ManufacturerLinkResource($vehicleData->manufacturer),
            'size_class' => $vehicleData->size ?? Arr::get($payload, 'Size'),

            'cross_section' => [
                'length' => Arr::get($payload, 'CrossSection.X'),
                'width' => Arr::get($payload, 'CrossSection.Y'),
                'height' => Arr::get($payload, 'CrossSection.Z'),
            ],

            'is_vehicle' => Arr::get($payload, 'IsVehicle'),
            'is_gravlev' => Arr::get($payload, 'IsGravlev'),
            'is_spaceship' => Arr::get($payload, 'IsSpaceship'),

            'signature' => [
                'ir_quantum' => Arr::get($payload, 'Emission.IrQuantum'),
                'ir_shields' => Arr::get($payload, 'Emission.IrShields'),

                'em_quantum' => Arr::get($payload, 'Emission.EmQuantum'),
                'em_shields' => Arr::get($payload, 'Emission.EmShields'),

                'em_groups_quantum' => Arr::get($payload, 'Emission.EmGroupsQuantum'),
                'em_groups_shields' => Arr::get($payload, 'Emission.EmGroupsShields'),

                'em_segment_groups_quantum' => Arr::get($payload, 'Emission.EmSegmentGroupsQuantum'),
                'em_segment_groups_shields' => Arr::get($payload, 'Emission.EmSegmentGroupsShields'),

                'em_per_segment' => Arr::get($payload, 'Emission.EmPerSegment'),
            ],

            'cooling' => [
                'generation_segments' => Arr::get($payload, 'Cooling.GenerationSegments'),
                'usage_shields_pct' => Arr::get($payload, 'Cooling.UsedSegmentsShieldsPct'),
                'usage_quantum_pct' => Arr::get($payload, 'Cooling.UsedSegmentsQuantumPct'),

                'used_segments_shields' => Arr::get($payload, 'Cooling.UsedSegmentsShields'),
                'used_segments_quantum' => Arr::get($payload, 'Cooling.UsedSegmentsQuantum'),

                'used_segments_shields_grouped' => Arr::get($payload, 'Cooling.UsedSegmentsShieldsGrouped'),
                'used_segments_quantum_grouped' => Arr::get($payload, 'Cooling.UsedSegmentsQuantumGrouped'),
            ],

            'power' => [
                'generation_segments' => Arr::get($payload, 'Power.GenerationSegments'),
                'used_segments_shields' => Arr::get($payload, 'Power.UsedSegmentsShields'),
                'used_segments_quantum' => Arr::get($payload, 'Power.UsedSegmentsQuantum'),
                'used_segments_grouped' => Arr::get($payload, 'Power.UsedSegmentsGrouped'),
            ],

            'penetration_multiplier' => [
                'fuse' => Arr::get($payload, 'PenetrationMultiplier.Fuse'),
                'components' => Arr::get($payload, 'PenetrationMultiplier.Components'),
            ],

            'insurance' => [
                'claim_time' => Arr::get($payload, 'Insurance.StandardClaimTime'),
                'expedite_time' => Arr::get($payload, 'Insurance.ExpeditedClaimTime'),
                'expedite_cost' => Arr::get($payload, 'Insurance.ExpeditedCost'),
            ],
            'damage_limits' => [
                'before_destruction' => Arr::get($payload, 'DamageBeforeDestruction'),
                'before_detach' => Arr::get($payload, 'DamageBeforeDetach'),
            ],
            $portKey => $hardpoints,
            'parts' => PartResource::collection(Arr::get($payload, 'Parts', [])),
            'turrets' => [
                'manned' => TurretSummaryResource::collection(Arr::get($payload, 'MannedTurrets', [])),
                'remote' => TurretSummaryResource::collection(Arr::get($payload, 'RemoteTurrets', [])),
            ],

            'career' => $vehicleData->career ?? Arr::get($payload, 'Career'),
            'role' => $vehicleData->role ?? Arr::get($payload, 'Role'),

            $this->mergeWhen(
                $this->whenLoaded('shipmatrixVehicle.components') && $this->isVehicleShowRoute($request),
                fn () => ['components' => $this->getComponents($vehicleData)]
            ),

            'web_url' => $this->buildWebUrl($request),
            'link' => route('vehicles.show', ['vehicle' => $this->uuid ?? $vehicleData->name]),

            'loaner' => $this->getLoaner($vehicleData),
            'skus' => $this->getSkus($vehicleData),
            'msrp' => $vehicleData->relationLoaded('shipMatrixVehicle')
                ? $vehicleData->shipMatrixVehicle?->msrp
                : null,
            'pledge_url' => $vehicleData->relationLoaded('shipMatrixVehicle')
                ? $vehicleData->shipMatrixVehicle?->pledge_url
                : null,

            'updated_at' => $vehicleData->updated_at,
            'version' => $vehicleData->relationLoaded('gameVersion')
                ? $vehicleData->gameVersion?->code
                : null,
        ];

        $this->loadShipMatrixData($data, $request);

        return $data;
    }

    private function buildSpeed(array $flight): ?array
    {
        return [
            'scm' => Arr::get($flight, 'Speeds.Scm'),
            'max' => Arr::get($flight, 'Speeds.Max'),
            'boost_forward' => Arr::get($flight, 'Speeds.BoostForward'),
            'boost_backward' => Arr::get($flight, 'Speeds.BoostBackward'),
            'zero_to_scm' => Arr::get($flight, 'Timing.ZeroToScm'),
            'zero_to_max' => Arr::get($flight, 'Timing.ZeroToMax'),
            'scm_to_zero' => Arr::get($flight, 'Timing.ScmToZero'),
            'max_to_zero' => Arr::get($flight, 'Timing.MaxToZero'),
        ];
    }

    private function buildAgility(array $flight): ?array
    {
        return [
            'pitch' => Arr::get($flight, 'AngularRates.Pitch'),
            'yaw' => Arr::get($flight, 'AngularRates.Yaw'),
            'roll' => Arr::get($flight, 'AngularRates.Roll'),
            'pitch_boosted' => Arr::get($flight, 'AngularRatesBoosted.Pitch'),
            'yaw_boosted' => Arr::get($flight, 'AngularRatesBoosted.Yaw'),
            'roll_boosted' => Arr::get($flight, 'AngularRatesBoosted.Roll'),

            'acceleration' => array_filter([
                'main' => Arr::get($flight, 'Acceleration.Raw.Forward'),
                'retro' => Arr::get($flight, 'Acceleration.Raw.Backward'),
                'vtol' => Arr::get($flight, 'Acceleration.Raw.Vtol'),
                'maneuvering' => Arr::get($flight, 'Acceleration.Raw.Maneuvering'),

                'main_g' => Arr::get($flight, 'Acceleration.RawG.Forward'),
                'retro_g' => Arr::get($flight, 'Acceleration.RawG.Backward'),
                'vtol_g' => Arr::get($flight, 'Acceleration.RawG.Vtol'),
                'maneuvering_g' => Arr::get($flight, 'Acceleration.RawG.Maneuvering'),
            ], static fn ($value) => $value !== null),
        ];
    }

    private function buildFuel(Collection $payload): ?array
    {
        return [
            'capacity' => Arr::get($payload, 'Propulsion.FuelCapacity') / 1000,
            'intake_rate' => Arr::get($payload, 'Propulsion.FuelIntakeRate'),
            'usage' => [
                'main' => Arr::get($payload, 'Propulsion.FuelUsage.Main'),
                'retro' => Arr::get($payload, 'Propulsion.FuelUsage.Retro'),
                'vtol' => Arr::get($payload, 'Propulsion.FuelUsage.Vtol'),
                'maneuvering' => Arr::get($payload, 'Propulsion.FuelUsage.Maneuvering'),
            ],
        ];
    }

    private function buildQuantum(Collection $payload): ?array
    {
        return [
            'quantum_speed' => Arr::get($payload, 'QuantumTravel.Speed'),
            'quantum_spool_time' => Arr::get($payload, 'QuantumTravel.SpoolTime'),
            'quantum_fuel_capacity' => Arr::get($payload, 'QuantumTravel.FuelCapacity') / 1000,
            'quantum_range' => Arr::get($payload, 'QuantumTravel.Range'),
            'port_olisar_to_arccorp_time' => Arr::get($payload, 'QuantumTravel.PortOlisarToArcCorpTime'),
            'port_olisar_to_arccorp_fuel' => Arr::get($payload, 'QuantumTravel.PortOlisarToArcCorpFuel'),
        ];
    }

    private function buildWebUrl(Request $request): string
    {
        $url = route('web.vehicles.show', ['vehicle' => $this->uuid]);
        $version = $request->query('version');

        if ($version === null || $version === '') {
            return $url;
        }

        return url()->query($url, ['version' => $version]);
    }

    /**
     * Adds Ship-Matrix information to the vehicle data.
     * Only adds non-empty fields from the Ship-Matrix vehicle.
     */
    private function loadShipMatrixData(array &$data, Request $request): void
    {
        $vehicleData = $this->data->first();

        if ($vehicleData === null) {
            return;
        }

        if (! $vehicleData->relationLoaded('shipMatrixVehicle')) {
            return;
        }

        $shipMatrixVehicle = $vehicleData->shipMatrixVehicle;

        if (! $shipMatrixVehicle->exists) {
            return;
        }

        $matrixVehicle = (new \App\Http\Resources\StarCitizen\Vehicle\VehicleResource($shipMatrixVehicle))
            ->resolve($request);

        $fieldMap = [
            'id' => 'id',
            'chassis_id' => 'chassis_id',
            'name' => 'shipmatrix_name',
            'foci' => 'foci',
            'production_status' => 'production_status',
            'production_note' => 'production_note',
            'type' => 'type',
            'description' => 'description',
            'size' => 'size',
            'msrp' => 'msrp',
            'pledge_url' => 'pledge_url',
            'loaner' => 'loaner',
            'skus' => 'skus',
            'components' => 'components',
        ];

        foreach ($fieldMap as $sourceKey => $targetKey) {
            if (array_key_exists($sourceKey, $matrixVehicle) && $matrixVehicle[$sourceKey] !== null) {
                $data[$targetKey] = $matrixVehicle[$sourceKey];
            }
        }
    }

    /**
     * Get components for the ship-matrix vehicle.
     */
    private function getComponents(VehicleData $vehicleData): array
    {
        if (! $vehicleData->relationLoaded('shipMatrixVehicle')) {
            return [];
        }

        $shipMatrixVehicle = $vehicleData->shipMatrixVehicle;

        if (! $shipMatrixVehicle->exists) {
            return [];
        }

        if (! $shipMatrixVehicle->relationLoaded('components')) {
            return [];
        }

        return ComponentResource::collection($shipMatrixVehicle->components)->resolve();
    }

    private function isVehicleShowRoute(Request $request): bool
    {
        return $request->routeIs('vehicles.show') || $request->routeIs('*.vehicles.show');
    }

    private function getApiVersion(Request $request): ?string
    {
        return $request->route('api_version');
    }

    private function getLoaner(VehicleData $vehicleData): array
    {
        if (! $vehicleData->relationLoaded('shipMatrixVehicle')) {
            return [];
        }

        $shipMatrixVehicle = $vehicleData->shipMatrixVehicle;

        if (! $shipMatrixVehicle || ! $shipMatrixVehicle->exists) {
            return [];
        }

        if (! $shipMatrixVehicle->relationLoaded('loaner')) {
            return [];
        }

        return VehicleLoanerResource::collection($shipMatrixVehicle->loaner)->resolve();
    }

    private function getSkus(VehicleData $vehicleData): array
    {
        if (! $vehicleData->relationLoaded('shipMatrixVehicle')) {
            return [];
        }

        $shipMatrixVehicle = $vehicleData->shipMatrixVehicle;

        if (! $shipMatrixVehicle || ! $shipMatrixVehicle->exists) {
            return [];
        }

        if (! $shipMatrixVehicle->relationLoaded('skus')) {
            return [];
        }

        return VehicleSkuResource::collection($shipMatrixVehicle->skus)->resolve();
    }
}
