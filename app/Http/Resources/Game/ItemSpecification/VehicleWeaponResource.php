<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\ItemSpecification;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'vehicle_weapon_damage_entry',
    title: 'Vehicle Weapon Damage Entry',
    description: 'Single damage component entry derived from Ammunition impact/detonation damage fields.',
    properties: [
        new OA\Property(property: 'type', description: 'Damage phase bucket.', type: 'string', example: 'impact', nullable: true),
        new OA\Property(property: 'name', description: 'Damage type name (lowercase).', type: 'string', example: 'physical', nullable: true),
        new OA\Property(property: 'damage', type: 'double', example: 11.5, nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'vehicle_weapon_mode',
    title: 'Vehicle Weapon Mode',
    description: 'Fire mode entry as emitted by the resource (no derived values).',
    properties: [
        new OA\Property(property: 'mode', type: 'string', example: 'Rapid', nullable: true),
        new OA\Property(property: 'localised', type: 'string', example: '[AUTO]', nullable: true),
        new OA\Property(property: 'type', type: 'string', example: 'rapid', nullable: true),
        new OA\Property(property: 'rpm', type: 'double', example: 925, nullable: true),
        new OA\Property(property: 'rounds_per_minute', description: 'Deprecated: Use rpm.', type: 'double', example: 925, nullable: true, deprecated: true),
        new OA\Property(property: 'ammo_per_shot', type: 'integer', example: 1, nullable: true),
        new OA\Property(property: 'pellets_per_shot', type: 'integer', example: 1, nullable: true),
        new OA\Property(property: 'damage_per_second', type: 'double', example: 0, nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'vehicle_weapon_damage_types',
    title: 'Vehicle Weapon Damage Types',
    description: 'Damage values split by type.',
    properties: [
        new OA\Property(property: 'physical', type: 'double', nullable: true),
        new OA\Property(property: 'energy', type: 'double', nullable: true),
        new OA\Property(property: 'distortion', type: 'double', nullable: true),
        new OA\Property(property: 'thermal', type: 'double', nullable: true),
        new OA\Property(property: 'biochemical', type: 'double', nullable: true),
        new OA\Property(property: 'stun', type: 'double', nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'vehicle_weapon_damage',
    title: 'Vehicle Weapon Damage',
    description: 'Damage summary block from stdItem.Weapon plus per-type alpha and dps from the primary mode.',
    properties: [
        new OA\Property(property: 'sustained_60s', type: 'double', nullable: true),
        new OA\Property(property: 'burst', type: 'double', nullable: true),
        new OA\Property(property: 'alpha_total', type: 'double', nullable: true),
        new OA\Property(property: 'max', type: 'double', nullable: true),
        new OA\Property(property: 'maximum', description: 'Deprecated: Use max.', type: 'double', nullable: true, deprecated: true),
        new OA\Property(property: 'alpha', ref: '#/components/schemas/vehicle_weapon_damage_types', nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'vehicle_weapon_spread',
    title: 'Vehicle Weapon Spread',
    properties: [
        new OA\Property(property: 'min', type: 'double', nullable: true),
        new OA\Property(property: 'max', type: 'double', nullable: true),
        new OA\Property(property: 'minimum', description: 'Deprecated: Use min.', type: 'double', nullable: true, deprecated: true),
        new OA\Property(property: 'maximum', description: 'Deprecated: Use max.', type: 'double', nullable: true, deprecated: true),
        new OA\Property(property: 'first_attack', type: 'double', nullable: true),
        new OA\Property(property: 'per_attack', type: 'double', nullable: true),
        new OA\Property(property: 'decay', type: 'double', nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'vehicle_weapon_barrel_spin_time',
    title: 'Vehicle Weapon Barrel Spin Time',
    properties: [
        new OA\Property(property: 'up', type: 'double', nullable: true),
        new OA\Property(property: 'down', type: 'double', nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'vehicle_weapon_heat',
    title: 'Vehicle Weapon Heat',
    properties: [
        new OA\Property(property: 'per_shot', type: 'double', nullable: true),
        new OA\Property(property: 'cooling_delay', type: 'double', nullable: true),
        new OA\Property(property: 'cooling_per_second', type: 'double', nullable: true),
        new OA\Property(property: 'overheat_max_shots', type: 'double', nullable: true),
        new OA\Property(property: 'overheat_max_time', type: 'double', nullable: true),
        new OA\Property(property: 'overheat_cooldown', type: 'double', nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'vehicle_weapon_capacitor',
    title: 'Vehicle Weapon Capacitor',
    properties: [
        new OA\Property(property: 'max_ammo_load', type: 'double', nullable: true),
        new OA\Property(property: 'regen_per_second', type: 'double', nullable: true),
        new OA\Property(property: 'cooldown', type: 'double', nullable: true),
        new OA\Property(property: 'requested_ammo_load', type: 'double', nullable: true),
        new OA\Property(property: 'costs_per_shot', type: 'double', nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'vehicle_weapon_charge',
    title: 'Vehicle Weapon Charge',
    properties: [
        new OA\Property(property: 'time', type: 'double', nullable: true),
        new OA\Property(property: 'overcharge_time', type: 'double', nullable: true),
        new OA\Property(property: 'overcharged_time', type: 'double', nullable: true),
        new OA\Property(property: 'cooldown_time', type: 'double', nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'vehicle_weapon_charge_modifier',
    title: 'Vehicle Weapon Charge Modifier',
    properties: [
        new OA\Property(property: 'damage', type: 'double', nullable: true),
        new OA\Property(property: 'fire_rate', type: 'double', nullable: true),
        new OA\Property(property: 'ammo_speed', type: 'double', nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'vehicle_weapon',
    title: 'Vehicle Weapon',
    description: 'Vehicle weapon stats derived from stdItem.Weapon and stdItem.Ammunition. Conditional blocks (spread, barrel_spin_time, heat, capacitor, charge, charge_modifier) may be omitted when source data is absent.',
    properties: [
        new OA\Property(
            property: 'class',
            description: 'Weapon class from stdItem.Weapon.WeaponClass.',
            type: 'string',
            example: 'LaserCannon',
            nullable: true
        ),
        new OA\Property(
            property: 'type',
            description: 'Item type from DescriptionData.Item Type.',
            type: 'string',
            example: 'Weapon',
            nullable: true
        ),
        new OA\Property(property: 'capacity', description: 'Ammunition capacity (stdItem.Ammunition.Capacity).', type: 'integer', example: 50, nullable: true),
        new OA\Property(property: 'range', description: 'Effective range in meters (stdItem.Weapon.EffectiveRange).', type: 'double', example: 1800, nullable: true),

        new OA\Property(
            property: 'rpm',
            description: 'Primary mode rounds per minute (Modes.0.RoundsPerMinute).',
            type: 'double',
            example: 400,
            nullable: true
        ),

        new OA\Property(
            property: 'damage',
            ref: '#/components/schemas/vehicle_weapon_damage',
            description: 'Damage summary and per-type alpha/dps.',
            nullable: true
        ),

        new OA\Property(
            property: 'modes',
            description: 'Fire modes as provided by the game data.',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/vehicle_weapon_mode'),
            nullable: true
        ),

        // Deprecated / backward-compatibility fields
        new OA\Property(
            property: 'damage_per_shot',
            description: 'Deprecated. Use `damage.alpha_total` (and/or `damage.alpha.*` for per-type values).',
            type: 'double',
            nullable: true,
            deprecated: true
        ),
        new OA\Property(
            property: 'damages',
            description: 'Deprecated. Use `damage` (and its `alpha`/`dps` breakdown).',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/vehicle_weapon_damage_entry'),
            nullable: true,
            deprecated: true
        ),
        new OA\Property(
            property: 'regeneration',
            description: 'Deprecated. Use `capacitor.regen_per_second`.',
            type: 'double',
            nullable: true,
            deprecated: true
        ),
        new OA\Property(
            property: 'ammunition',
            description: 'Deprecated: use ammunition from the root resource.',
            type: 'object',
            nullable: true,
            deprecated: true
        ),

        // Conditional blocks (present only when source fields exist)
        new OA\Property(property: 'spread', ref: '#/components/schemas/vehicle_weapon_spread', nullable: true),
        new OA\Property(property: 'barrel_spin_time', ref: '#/components/schemas/vehicle_weapon_barrel_spin_time', nullable: true),
        new OA\Property(property: 'heat', ref: '#/components/schemas/vehicle_weapon_heat', nullable: true),
        new OA\Property(property: 'capacitor', ref: '#/components/schemas/vehicle_weapon_capacitor', nullable: true),
        new OA\Property(property: 'charge', ref: '#/components/schemas/vehicle_weapon_charge', nullable: true),
        new OA\Property(property: 'charge_modifier', ref: '#/components/schemas/vehicle_weapon_charge_modifier', nullable: true),
    ],
    type: 'object'
)]
class VehicleWeaponResource extends AbstractItemSpecificationResource
{
    public function toArray(Request $request): array
    {
        $ammo = $this->extractFromStdItem($this->resource, 'Ammunition');
        $weapon = $this->extractFromStdItem($this->resource, 'Weapon');
        $mode = Arr::get($weapon, 'Modes.0');
        $heat = Arr::get($weapon, 'Heat');

        $damages = array_filter([
            ['type' => 'impact', 'name' => 'physical', 'damage' => Arr::get($ammo, 'ImpactDamage.Physical')],
            ['type' => 'impact', 'name' => 'energy', 'damage' => Arr::get($ammo, 'ImpactDamage.Energy')],
            ['type' => 'impact', 'name' => 'distortion', 'damage' => Arr::get($ammo, 'ImpactDamage.Distortion')],
            ['type' => 'impact', 'name' => 'thermal', 'damage' => Arr::get($ammo, 'ImpactDamage.Thermal')],
            ['type' => 'impact', 'name' => 'biochemical', 'damage' => Arr::get($ammo, 'ImpactDamage.Biochemical')],
            ['type' => 'impact', 'name' => 'stun', 'damage' => Arr::get($ammo, 'ImpactDamage.Stun')],

            ['type' => 'detonation', 'name' => 'physical', 'damage' => Arr::get($ammo, 'DetonationDamage.Physical')],
            ['type' => 'detonation', 'name' => 'energy', 'damage' => Arr::get($ammo, 'DetonationDamage.Energy')],
            ['type' => 'detonation', 'name' => 'distortion', 'damage' => Arr::get($ammo, 'DetonationDamage.Distortion')],
            ['type' => 'detonation', 'name' => 'thermal', 'damage' => Arr::get($ammo, 'DetonationDamage.Thermal')],
            ['type' => 'detonation', 'name' => 'biochemical', 'damage' => Arr::get($ammo, 'DetonationDamage.Biochemical')],
            ['type' => 'detonation', 'name' => 'stun', 'damage' => Arr::get($ammo, 'DetonationDamage.Stun')],
        ], static fn (array $entry) => $entry !== [] && ! empty($entry['damage']));

        $modes = collect(Arr::get($weapon, 'Modes', []))
            ->map(static fn (mixed $mode): array => [
                'mode' => Arr::get($mode, 'Name'),
                'localised' => Arr::get($mode, 'LocalisedName'),
                'type' => Arr::get($mode, 'FireType'),
                'rpm' => Arr::get($mode, 'RoundsPerMinute'),
                'rounds_per_minute' => Arr::get($mode, 'RoundsPerMinute'),  // deprecated: use rpm
                'ammo_per_shot' => Arr::get($mode, 'AmmoPerShot'),
                'pellets_per_shot' => Arr::get($mode, 'PelletsPerShot'),
                'damage_per_second' => Arr::get($mode, 'DamagePerSecond'),
            ])
            ->values()
            ->toArray();

        return [
            'class' => Arr::get($weapon, 'WeaponClass'),
            'type' => $this->extractFromStdItem($this->resource, 'DescriptionData.Item Type'),
            'capacity' => Arr::get($ammo, 'Capacity'),
            'range' => Arr::get($weapon, 'EffectiveRange'),

            // deprecated
            'damage_per_shot' => Arr::get($mode, 'Alpha'),
            'regeneration' => Arr::get($weapon, 'Capacitor.MaxRegenPerSec'),

            'rpm' => Arr::get($mode, 'RoundsPerMinute'),

            'damages' => $damages,
            'modes' => $modes,

            'damage' => [
                'sustained_60s' => Arr::get($weapon, 'Damage.Sustained60s'),
                'burst' => Arr::get($weapon, 'Damage.Burst'),
                'alpha_total' => Arr::get($weapon, 'Damage.AlphaTotal'),
                'max' => Arr::get($weapon, 'Damage.Maximum'),
                'maximum' => Arr::get($weapon, 'Damage.Maximum'),  // deprecated: use max
                'dps' => [
                    'physical' => Arr::get($mode, 'DpsPhysical'),
                    'energy' => Arr::get($mode, 'DpsEnergy'),
                    'distortion' => Arr::get($mode, 'DpsDistortion'),
                    'thermal' => Arr::get($mode, 'DpsThermal'),
                    'biochemical' => Arr::get($mode, 'DpsBiochemical'),
                    'stun' => Arr::get($mode, 'DpsStun'),
                ],
                'alpha' => [
                    'physical' => Arr::get($mode, 'AlphaPhysical'),
                    'energy' => Arr::get($mode, 'AlphaEnergy'),
                    'distortion' => Arr::get($mode, 'AlphaDistortion'),
                    'thermal' => Arr::get($mode, 'AlphaThermal'),
                    'biochemical' => Arr::get($mode, 'AlphaBiochemical'),
                    'stun' => Arr::get($mode, 'AlphaStun'),
                ],
            ],

            $this->mergeWhen(Arr::get($mode, 'Spread') !== null, [
                'spread' => [
                    'min' => Arr::get($mode, 'Spread.Minimum'),
                    'max' => Arr::get($mode, 'Spread.Maximum'),
                    'minimum' => Arr::get($mode, 'Spread.Minimum'),  // deprecated: use min
                    'maximum' => Arr::get($mode, 'Spread.Maximum'),  // deprecated: use max
                    'first_attack' => Arr::get($mode, 'Spread.FirstAttack'),
                    'per_attack' => Arr::get($mode, 'Spread.Attack'),
                    'decay' => Arr::get($mode, 'Spread.Decay'),
                ],
            ]),

            $this->mergeWhen(Arr::get($mode, 'BarrelSpinTime') !== null, [
                'barrel_spin_time' => [
                    'up' => Arr::get($mode, 'BarrelSpinTime.Up'),
                    'down' => Arr::get($mode, 'BarrelSpinTime.Down'),
                ],
            ]),

            $this->mergeWhen(! empty($heat), [
                'heat' => [
                    'per_shot' => Arr::get($heat, 'HeatPerShot'),
                    'cooling_delay' => Arr::get($heat, 'CoolingDelay'),
                    'cooling_per_second' => Arr::get($heat, 'CoolingPerSecond'),
                    'overheat_max_shots' => Arr::get($heat, 'ShotsToOverheat'),
                    'overheat_max_time' => Arr::get($heat, 'TimeToOverheat'),
                    'overheat_cooldown' => Arr::get($heat, 'OverheatFixTime'),
                ],
            ]),

            $this->mergeWhen(Arr::get($weapon, 'Capacitor.MaxAmmoLoad') !== null, [
                'capacitor' => [
                    'max_ammo_load' => Arr::get($weapon, 'Capacitor.MaxAmmoLoad'),
                    'regen_per_second' => Arr::get($weapon, 'Capacitor.MaxRegenPerSec'),
                    'cooldown' => Arr::get($weapon, 'Capacitor.Cooldown'),

                    'requested_ammo_load' => Arr::get($weapon, 'Capacitor.RequestedAmmoLoad'),
                    'costs_per_shot' => Arr::get($weapon, 'Capacitor.CostPerBullet'),
                ],
            ]),

            $this->mergeWhen(Arr::get($mode, 'Charge') !== null, [
                'charge' => [
                    'time' => Arr::get($mode, 'Charge.ChargeTime'),
                    'overcharge_time' => Arr::get($mode, 'Charge.OverchargeTime'),
                    'overcharged_time' => Arr::get($mode, 'Charge.OverchargedTime'),
                    'cooldown_time' => Arr::get($mode, 'Charge.CooldownTime'),
                ],
                'charge_modifier' => [
                    'damage' => Arr::get($mode, 'ChargeModifier.Damage'),
                    'fire_rate' => Arr::get($mode, 'ChargeModifier.FireRate'),
                    'ammo_speed' => Arr::get($mode, 'ChargeModifier.AmmoSpeed'),
                ],
            ]),

            'ammunition' => new AmmunitionResource($this->resource),
        ];
    }
}
