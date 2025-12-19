<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Vehicle;

use App\Http\Resources\AbstractBaseResource;
use App\Http\Resources\Game\Manufacturer\ManufacturerLinkResource;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'game_vehicle',
    title: 'Game Vehicle',
    description: 'Vehicle data imported from scunpacked ship files for a specific game version.',
    properties: [
        new OA\Property(property: 'uuid', type: 'string', example: 'b5892cde-e805-4626-94a2-89fdf6eedc1c'),
        new OA\Property(property: 'name', type: 'string', example: 'Avenger Titan'),
        new OA\Property(property: 'slug', type: 'string', example: 'avenger-titan'),
        new OA\Property(property: 'class_name', type: 'string', example: 'AEGS_Avenger_Titan'),
        new OA\Property(property: 'manufacturer', ref: '#/components/schemas/manufacturer_link'),
        new OA\Property(property: 'size', type: 'integer', example: 2, nullable: true),
        new OA\Property(
            property: 'dimensions',
            properties: [
                new OA\Property(property: 'length', type: 'number', example: 18.0),
                new OA\Property(property: 'width', type: 'number', example: 16.0),
                new OA\Property(property: 'height', type: 'number', example: 5.0),
            ],
            type: 'object'
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
                new OA\Property(property: 'infrared', type: 'number', example: 1),
                new OA\Property(property: 'electromagnetic', type: 'number', example: 1),
                new OA\Property(property: 'cross_section', type: 'number', example: 1),
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
            property: 'description_data',
            description: 'Key/value pairs parsed from the description block in the game files.',
            type: 'object',
            example: ['Manufacturer' => 'Aegis Dynamics', 'Focus' => 'Light Freight'],
            nullable: true
        ),
        new OA\Property(property: 'career', type: 'string', example: 'Light Freight', nullable: true),
        new OA\Property(property: 'role', type: 'string', example: 'Combat', nullable: true),
        new OA\Property(property: 'description', type: 'string', nullable: true),
        new OA\Property(property: 'updated_at', type: 'string'),
        new OA\Property(property: 'version', type: 'string', example: '4.4.0-LIVE.12340123'),
    ],
    type: 'object'
)]
class VehicleResource extends AbstractBaseResource
{
    public static function validIncludes(): array
    {
        return [
            'ports',
            'parts',
            'cargo_grids',
            'turrets',
        ];
    }

    public function toArray(Request $request): array
    {
        $vehicleData = $this->data->first();

        if ($vehicleData === null) {
            return [];
        }

        $payload = $vehicleData->json ?? [];
        $flight = Arr::get($payload, 'FlightCharacteristics', []);
        $agility = Arr::get($payload, 'Agility', []);
        $quantum = Arr::get($payload, 'Quantum', []);
        $quantumTravel = Arr::get($payload, 'QuantumTravel', []);
        $fuel = Arr::get($payload, 'Fuel', []);

        $ports = PortResource::collection(Arr::get($payload, 'Ports', []));
        $cargoGrids = CargoGridResource::collection(Arr::get($payload, 'CargoGrids', []));
        $parts = PartResource::collection(Arr::get($payload, 'Parts', []));

        return array_filter([
            'uuid' => $vehicleData->vehicle?->uuid ?? Arr::get($payload, 'UUID') ?? $vehicleData->uuid,
            'name' => $vehicleData->name ?? Arr::get($payload, 'Name'),
            'slug' => Str::slug($vehicleData->name ?? Arr::get($payload, 'Name', '')),
            'class_name' => $vehicleData->class_name ?? Arr::get($payload, 'ClassName'),
            'manufacturer' => new ManufacturerLinkResource($vehicleData->manufacturer),
            'size' => $vehicleData->size ?? Arr::get($payload, 'Size'),
            'dimensions' => [
                'length' => $vehicleData->length ?? Arr::get($payload, 'Length'),
                'width' => $vehicleData->width ?? Arr::get($payload, 'Width'),
                'height' => $vehicleData->height ?? Arr::get($payload, 'Height'),
            ],
            'mass' => $vehicleData->mass ?? Arr::get($payload, 'Mass'),
            'cargo_capacity' => $vehicleData->cargo ?? Arr::get($payload, 'Cargo'),
            'cargo_scu' => Arr::get($payload, 'Cargo'),
            'cargo_grids' => $cargoGrids,
            'crew' => $vehicleData->crew ?? Arr::get($payload, 'Crew'),
            'is_vehicle' => Arr::get($payload, 'IsVehicle'),
            'is_gravlev' => Arr::get($payload, 'IsGravlev'),
            'is_spaceship' => Arr::get($payload, 'IsSpaceship'),
            'health' => Arr::get($payload, 'Health'),
            'shield_hp' => Arr::get($payload, 'ShieldHp'),
            'shield_face_type' => Arr::get($payload, 'ShieldFaceType'),
            'speed' => $this->buildSpeed($flight),
            'agility' => $this->buildAgility($agility, $flight),
            'afterburner' => Arr::get($flight, 'Afterburner'),
            'fuel' => $this->buildFuel($fuel, $payload),
            'quantum' => $this->buildQuantum($quantum, $quantumTravel),
            'inventory' => [
                'personal' => Arr::get($payload, 'PersonalInventory'),
                'vehicle' => Arr::get($payload, 'VehicleInventory'),
            ],
            'em_signature' => [
                'infrared' => Arr::get($payload, 'Armor.SignalInfrared'),
                'electromagnetic' => Arr::get($payload, 'Armor.SignalElectromagnetic'),
                'cross_section' => Arr::get($payload, 'Armor.SignalCrossSection'),
            ],
            'armor' => [
                'physical' => Arr::get($payload, 'Armor.DamagePhysical'),
                'energy' => Arr::get($payload, 'Armor.DamageEnergy'),
                'distortion' => Arr::get($payload, 'Armor.DamageDistortion'),
                'thermal' => Arr::get($payload, 'Armor.DamageThermal'),
                'biochemical' => Arr::get($payload, 'Armor.DamageBiochemical'),
                'stun' => Arr::get($payload, 'Armor.DamageStun'),
            ],
            'insurance' => [
                'standard_claim_time' => Arr::get($payload, 'Insurance.StandardClaimTime'),
                'expedited_claim_time' => Arr::get($payload, 'Insurance.ExpeditedClaimTime'),
                'expedited_cost' => Arr::get($payload, 'Insurance.ExpeditedCost'),
            ],
            'damage_limits' => [
                'before_destruction' => Arr::get($payload, 'DamageBeforeDestruction'),
                'before_detach' => Arr::get($payload, 'DamageBeforeDetach'),
            ],
            'ports' => $ports,
            'parts' => $parts,
            'turrets' => [
                'manned' => TurretSummaryResource::collection(Arr::get($payload, 'MannedTurrets', [])),
                'remote' => TurretSummaryResource::collection(Arr::get($payload, 'RemoteTurrets', [])),
            ],
            'description_data' => Arr::get($payload, 'DescriptionData'),
            'career' => Arr::get($payload, 'Career'),
            'role' => Arr::get($payload, 'Role'),
            'description' => Arr::get($payload, 'DescriptionText') ?? Arr::get($payload, 'Description'),
            'updated_at' => $vehicleData->updated_at,
            'version' => $vehicleData->gameVersion?->code,
        ], static fn ($value) => $value !== null && $value !== []);
    }

    private function buildSpeed(array $flight): ?array
    {
        $speed = [
            'scm' => Arr::get($flight, 'ScmSpeed'),
            'max' => Arr::get($flight, 'MaxSpeed'),
            'boost_forward' => Arr::get($flight, 'BoostSpeedForward'),
            'boost_backward' => Arr::get($flight, 'BoostSpeedBackward'),
            'zero_to_scm' => Arr::get($flight, 'ZeroToScm'),
            'zero_to_max' => Arr::get($flight, 'ZeroToMax'),
            'scm_to_zero' => Arr::get($flight, 'ScmToZero'),
            'max_to_zero' => Arr::get($flight, 'MaxToZero'),
        ];

        return array_filter($speed, static fn ($value) => $value !== null);
    }

    private function buildAgility(array $agility, array $flight): ?array
    {
        $data = [
            'pitch' => Arr::get($agility, 'Pitch', Arr::get($flight, 'Pitch')),
            'yaw' => Arr::get($agility, 'Yaw', Arr::get($flight, 'Yaw')),
            'roll' => Arr::get($agility, 'Roll', Arr::get($flight, 'Roll')),
            'acceleration' => array_filter([
                'main' => Arr::get($agility, 'Acceleration.Main', Arr::get($flight, 'Acceleration.Main')),
                'retro' => Arr::get($agility, 'Acceleration.Retro', Arr::get($flight, 'Acceleration.Retro')),
                'vtol' => Arr::get($agility, 'Acceleration.Vtol', Arr::get($flight, 'Acceleration.Vtol')),
                'maneuvering' => Arr::get($agility, 'Acceleration.Maneuvering', Arr::get($flight, 'Acceleration.Maneuvering')),
                'main_g' => Arr::get($agility, 'Acceleration.MainG', Arr::get($flight, 'AccelerationG.Main')),
                'retro_g' => Arr::get($agility, 'Acceleration.RetroG', Arr::get($flight, 'AccelerationG.Retro')),
                'vtol_g' => Arr::get($agility, 'Acceleration.VtolG', Arr::get($flight, 'AccelerationG.Vtol')),
                'maneuvering_g' => Arr::get($agility, 'Acceleration.ManeuveringG', Arr::get($flight, 'AccelerationG.Maneuvering')),
            ], static fn ($value) => $value !== null),
        ];

        return array_filter($data, static fn ($value) => $value !== null && $value !== []);
    }

    private function buildFuel(array $fuel, array $payload): ?array
    {
        $data = [
            'capacity' => Arr::get($fuel, 'Capacity', Arr::get($payload, 'Propulsion.FuelCapacity')),
            'intake_rate' => Arr::get($fuel, 'IntakeRate', Arr::get($payload, 'Propulsion.FuelIntakeRate')),
            'usage' => Arr::get($fuel, 'Usage', Arr::get($payload, 'Propulsion.FuelUsage')),
        ];

        return array_filter($data, static fn ($value) => $value !== null);
    }

    private function buildQuantum(array $quantum, array $quantumTravel): ?array
    {
        $data = [
            'speed' => Arr::get($quantum, 'QuantumSpeed', Arr::get($quantumTravel, 'Speed')),
            'spool_time' => Arr::get($quantum, 'QuantumSpoolTime', Arr::get($quantumTravel, 'SpoolTime')),
            'fuel_capacity' => Arr::get($quantum, 'QuantumFuelCapacity', Arr::get($quantumTravel, 'FuelCapacity')),
            'range' => Arr::get($quantum, 'QuantumRange', Arr::get($quantumTravel, 'Range')),
            'port_olisar_to_arccorp_time' => Arr::get($quantumTravel, 'PortOlisarToArcCorpTime'),
            'port_olisar_to_arccorp_fuel' => Arr::get($quantumTravel, 'PortOlisarToArcCorpFuel'),
        ];

        return array_filter($data, static fn ($value) => $value !== null);
    }
}
