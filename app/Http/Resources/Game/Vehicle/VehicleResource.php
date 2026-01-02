<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Vehicle;

use App\Http\Resources\AbstractBaseResource;
use App\Http\Resources\Game\Concerns\ExtractsJsonData;
use App\Http\Resources\Game\Manufacturer\ManufacturerLinkResource;
use App\Http\Resources\StarCitizen\Vehicle\ComponentResource;
use App\Models\Game\Vehicle;
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
        new OA\Property(property: 'display_name', type: 'string', example: 'Avenger Titan', nullable: true),
        new OA\Property(property: 'slug', type: 'string', example: 'avenger-titan'),
        new OA\Property(property: 'class_name', type: 'string', example: 'AEGS_Avenger_Titan'),
        new OA\Property(property: 'manufacturer', ref: '#/components/schemas/manufacturer_link'),
        new OA\Property(property: 'size', type: 'integer', example: 2, nullable: true),
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
                new OA\Property(property: 'width', type: 'number', example: 16.0),
                new OA\Property(property: 'height', type: 'number', example: 5.0),
            ],
            type: 'object',
            deprecated: true
        ),
        new OA\Property(property: 'mass', type: 'number', example: 53531.0, nullable: true),
        new OA\Property(property: 'cargo_capacity', type: 'number', example: 8, nullable: true, deprecated: true),
        new OA\Property(property: 'cargo_scu', type: 'number', example: 8, nullable: true),
        new OA\Property(
            property: 'cargo_grids',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/game_vehicle_cargo_grid'),
            nullable: true
        ),
        new OA\Property(property: 'crew', type: 'integer', example: 1, nullable: true),
        new OA\Property(property: 'is_vehicle', type: 'boolean', example: false, nullable: true),
        new OA\Property(property: 'is_gravlev', type: 'boolean', example: false, nullable: true),
        new OA\Property(property: 'is_spaceship', type: 'boolean', example: true, nullable: true),
        new OA\Property(property: 'health', type: 'number', example: 2500, nullable: true),
        new OA\Property(property: 'shield_hp', type: 'number', example: 12000, nullable: true),
        new OA\Property(property: 'shield_face_type', type: 'string', example: 'FourFaces', nullable: true),
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
            description: 'Afterburner capacitor and timing straight from FlightCharacteristics.Afterburner.',
            properties: [
                new OA\Property(property: 'capacitor', type: 'number', example: 20, nullable: true),
                new OA\Property(property: 'idle_cost', type: 'number', example: 1, nullable: true),
                new OA\Property(property: 'linear_cost', type: 'number', example: 0, nullable: true),
                new OA\Property(property: 'angular_cost', type: 'number', example: 0, nullable: true),
                new OA\Property(property: 'regen_per_sec', type: 'number', example: 0.75, nullable: true),
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
                new OA\Property(property: 'speed', type: 'number', example: 165000000),
                new OA\Property(property: 'spool_time', type: 'number', example: 4),
                new OA\Property(property: 'fuel_capacity', type: 'number', example: 1.1, nullable: true),
                new OA\Property(property: 'range', type: 'number', example: 112244897.9592, nullable: true),
                new OA\Property(property: 'port_olisar_to_arccorp_time', type: 'number', example: 254.105158, nullable: true),
                new OA\Property(property: 'port_olisar_to_arccorp_fuel', type: 'number', example: 410.888040486, nullable: true),
            ],
            type: 'object',
            nullable: true
        ),
        new OA\Property(
            property: 'inventory',
            properties: [
                new OA\Property(property: 'personal', type: 'number', example: 0.25, nullable: true),
                new OA\Property(property: 'vehicle', type: 'number', example: 1, nullable: true),
            ],
            type: 'object',
            nullable: true
        ),
        new OA\Property(
            property: 'em_signature',
            properties: [
                new OA\Property(property: 'infrared', type: 'number', example: 4412),
                new OA\Property(property: 'em_quantum', description: 'EM Signature with quantum drive active', type: 'number', example: 30458),
                new OA\Property(property: 'em_shields', description: 'EM Signature with shields active', type: 'number', example: 14320),
                new OA\Property(property: 'electromagnetic', description: 'Deprecated, use armor.signal.electromagnetic instead.', type: 'number', example: 1, deprecated: true),
                new OA\Property(property: 'cross_section', description: 'Deprecated, use armor.signal.cross_section instead.', type: 'number', example: 1, deprecated: true),
            ],
            type: 'object',
            nullable: true
        ),
        new OA\Property(
            property: 'armor',
            properties: [
                new OA\Property(property: 'physical', type: 'number', example: 0.62, nullable: true),
                new OA\Property(property: 'energy', type: 'number', example: 1, nullable: true),
                new OA\Property(property: 'distortion', type: 'number', example: 1, nullable: true),
                new OA\Property(property: 'thermal', type: 'number', example: 1, nullable: true),
                new OA\Property(property: 'biochemical', type: 'number', example: 1, nullable: true),
                new OA\Property(property: 'stun', type: 'number', example: 0, nullable: true),
                new OA\Property(property: 'penetration_resistance', properties: [
                    new OA\Property(property: 'base', type: 'number', example: 0.001, nullable: true),
                    new OA\Property(property: 'physical', type: 'number', example: 0.001, nullable: true),
                    new OA\Property(property: 'energy', type: 'number', example: 0.001, nullable: true),
                    new OA\Property(property: 'distortion', type: 'number', example: 0.001, nullable: true),
                    new OA\Property(property: 'thermal', type: 'number', example: 0.001, nullable: true),
                    new OA\Property(property: 'biochemical', type: 'number', example: 0.001, nullable: true),
                    new OA\Property(property: 'stun', type: 'number', example: 0.001, nullable: true),
                ]),
                new OA\Property(property: 'signal', properties: [
                    new OA\Property(property: 'infrared', type: 'number', example: 1),
                    new OA\Property(property: 'electromagnetic', type: 'number', example: 1),
                    new OA\Property(property: 'cross_section', type: 'number', example: 1),
                ]),
            ],
            type: 'object',
            nullable: true
        ),
        new OA\Property(
            property: 'insurance',
            properties: [
                new OA\Property(property: 'standard_claim_time', type: 'number', example: 4.05, nullable: true),
                new OA\Property(property: 'expedited_claim_time', type: 'number', example: 1.35, nullable: true),
                new OA\Property(property: 'expedited_cost', type: 'number', example: 2343, nullable: true),
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
            property: 'item_description_data',
            description: 'Description data from the vehicle\'s ItemData. Returns null when no matching ItemData exists.',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/item_description_data'),
            nullable: true
        ),
        new OA\Property(
            property: 'description_data',
            description: 'Key/value pairs parsed from the description block in the game files.',
            type: 'object',
            example: ['Manufacturer' => 'Aegis Dynamics', 'Focus' => 'Light Freight'],
            nullable: true
        ),
        new OA\Property(property: 'career', type: 'string', example: 'Light Freight', nullable: true),
        new OA\Property(property: 'role', type: 'string', example: 'Combat', nullable: true),
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
            items: new OA\Items(ref: '#/components/schemas/translation_v2'),
            nullable: true
        ),
        new OA\Property(
            property: 'production_status',
            ref: '#/components/schemas/translation_v2',
            description: 'Ship-Matrix production status',
            nullable: true
        ),
        new OA\Property(
            property: 'production_note',
            ref: '#/components/schemas/translation_v2',
            description: 'Ship-Matrix production note',
            nullable: true
        ),
        new OA\Property(
            property: 'type',
            ref: '#/components/schemas/translation_v2',
            description: 'Ship-Matrix vehicle type',
            nullable: true
        ),
        new OA\Property(
            property: 'shipmatrix_description',
            ref: '#/components/schemas/translation_v2',
            description: 'Ship-Matrix vehicle description',
            nullable: true
        ),
        new OA\Property(
            property: 'size_name',
            ref: '#/components/schemas/translation_v2',
            description: 'Ship-Matrix size name (Small, Medium, Large, etc.)',
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
            items: new OA\Items(ref: '#/components/schemas/vehicle_component_v2'),
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
            'manufacturer',
            'shipMatrixVehicle',
            'components',
        ];
    }

    public function toArray(Request $request): array
    {
        $includes = collect(explode(',', $request->get('include', '')))
            ->map('trim')
            ->map('strtolower')
            ->toArray();

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

            'mass_hull' => round($vehicleData->mass_vehicle ?? Arr::get($payload, 'Mass'), 2),
            'mass_loadout' => round($vehicleData->mass_loadout ?? Arr::get($payload, 'MassLoadout'), 2),
            'mass_total' => round($vehicleData->mass_total ?? Arr::get($payload, 'MassTotal'), 2),

            'cargo_capacity' => $vehicleData->cargo ?? Arr::get($payload, 'Cargo'),
            'cargo_grids' => $cargoGrids,
            $this->mergeWhen(
                $cargoLimits !== null,
                fn () => ['cargo_limits' => $cargoLimits]
            ),
            'vehicle_inventory' => Arr::get($payload, 'Stowage'),
            'inventory_containers' => Arr::get($payload, 'InventoryContainers'),

            'crew' => [
                'min' => Arr::get($payload, 'Crew'),
                'max' => Arr::get($payload, 'Crew'), // TODO
                'weapon' => Arr::get($payload, 'WeaponCrew'),
                'operation' => null, // TODO
            ],

            'health' => Arr::get($payload, 'Health'),

            'shield_hp' => Arr::get($payload, 'ShieldsTotal.Hp'),
            'shield_face_type' => Arr::get($payload, 'ShieldController.FaceType'),

            'shield' => [
                'hp' => Arr::get($payload, 'ShieldsTotal.Hp'),
                'regeneration' => round(Arr::get($payload, 'ShieldsTotal.Regen'), 2),
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
                'usage_shields_pct' => Arr::get($payload, 'Cooling.UsageShieldsPct'),
                'usage_quantum_pct' => Arr::get($payload, 'Cooling.UsageQuantumPct'),

                'used_segments_shields' => Arr::get($payload, 'Cooling.UsedSegmentsShields'),
                'used_segments_quantum' => Arr::get($payload, 'Cooling.UsedSegmentsQuantum'),
            ],

            'power' => [
                'used_segments_shields' => Arr::get($payload, 'Power.UsedSegmentsShields'),
                'used_segments_quantum' => Arr::get($payload, 'Power.UsedSegmentsQuantum'),
                'generation_segments' => Arr::get($payload, 'Power.GenerationSegments'),
                'usage' => Arr::get($payload, 'Power.Usage'),
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

            'career' => Arr::get($payload, 'Career'),
            'role' => Arr::get($payload, 'Role'),

            $this->mergeWhen(
                $this->shouldIncludeComponents($includes, $vehicleData),
                fn () => ['components' => $this->getComponents($vehicleData)]
            ),

            'updated_at' => $vehicleData->updated_at,
            'version' => $vehicleData->gameVersion?->code,
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
                'main' => round(Arr::get($flight, 'Acceleration.Raw.Forward', 0), 2),
                'retro' => round(Arr::get($flight, 'Acceleration.Raw.Backward', 0), 2),
                'vtol' => round(Arr::get($flight, 'Acceleration.Raw.Vtol', 0), 2),
                'maneuvering' => round(Arr::get($flight, 'Acceleration.Raw.Maneuvering', 0), 2),

                'main_g' => round(Arr::get($flight, 'Acceleration.RawG.Forward', 0), 2),
                'retro_g' => round(Arr::get($flight, 'Acceleration.RawG.Backward', 0), 2),
                'vtol_g' => round(Arr::get($flight, 'Acceleration.RawG.Vtol', 0), 2),
                'maneuvering_g' => round(Arr::get($flight, 'Acceleration.RawG.Maneuvering', 0), 2),
            ], static fn ($value) => $value !== null),
        ];
    }

    private function buildFuel(Collection $payload): ?array
    {
        return [
            'capacity' => Arr::get($payload, 'Propulsion.FuelCapacity') / 1000,
            'intake_rate' => Arr::get($payload, 'Propulsion.FuelIntakeRate'),
            'usage' => [
                'main' => round(Arr::get($payload, 'Propulsion.FuelUsage.Main'), 2),
                'retro' => round(Arr::get($payload, 'Propulsion.FuelUsage.Retro'), 2),
                'vtol' => round(Arr::get($payload, 'Propulsion.FuelUsage.Vtol'), 2),
                'maneuvering' => round(Arr::get($payload, 'Propulsion.FuelUsage.Maneuvering'), 2),
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
        ];

        foreach ($fieldMap as $sourceKey => $targetKey) {
            if (array_key_exists($sourceKey, $matrixVehicle) && $matrixVehicle[$sourceKey] !== null) {
                $data[$targetKey] = $matrixVehicle[$sourceKey];
            }
        }
    }

    /**
     * Determine if components should be included in the response.
     */
    private function shouldIncludeComponents(array $includes, ?VehicleData $vehicleData): bool
    {
        if (! in_array('components', $includes, true) && ! in_array('shipmatrixvehicle.components', $includes, true)) {
            return false;
        }

        if ($vehicleData === null) {
            return false;
        }

        if (! $vehicleData->relationLoaded('shipMatrixVehicle')) {
            return false;
        }

        $shipMatrixVehicle = $vehicleData->shipMatrixVehicle;

        if (! $shipMatrixVehicle->exists) {
            return false;
        }

        return $shipMatrixVehicle->relationLoaded('components');
    }

    /**
     * Get components for the ship-matrix vehicle.
     */
    private function getComponents(VehicleData $vehicleData): array
    {
        $shipMatrixVehicle = $vehicleData->shipMatrixVehicle;

        return ComponentResource::collection($shipMatrixVehicle->components)->resolve();
    }

    private function getApiVersion(Request $request): ?string
    {
        return $request->route('api_version');
    }
}
