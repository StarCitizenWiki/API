<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\ItemSpecification;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'vehicle_weapon',
    title: 'Vehicle Weapon',
    description: 'Vehicle weapon stats derived from stdItem.Weapon.',
    properties: [
        new OA\Property(property: 'class', description: 'V2 compatibility field', type: 'string', nullable: true, deprecated: true),
        new OA\Property(property: 'type', description: 'Deprecated: Use value from description_data', type: 'string', nullable: true, deprecated: true),
        new OA\Property(property: 'speed', type: 'double', nullable: true),
        new OA\Property(property: 'range', type: 'double', nullable: true),
        new OA\Property(property: 'size', type: 'integer', nullable: true),
        new OA\Property(property: 'capacity', type: 'integer', nullable: true),
        new OA\Property(property: 'damage_per_shot', description: 'Deprecated: Use damage.alpha_total', type: 'double', nullable: true, deprecated: true),
        new OA\Property(
            property: 'damages',
            description: 'Deprecated: Use damage.alpha',
            properties: [
                new OA\Property(property: 'impact', type: 'object', nullable: true),
                new OA\Property(property: 'detonation', type: 'object', nullable: true),
            ],
            type: 'object',
            nullable: true,
            deprecated: true
        ),
        new OA\Property(
            property: 'modes',
            type: 'array',
            items: new OA\Items(type: 'object'),
            nullable: true
        ),
        new OA\Property(
            property: 'regeneration',
            description: 'Deprecated: Use capacitor.regen_per_second',
            type: 'object',
            nullable: true,
            deprecated: true
        ),
        new OA\Property(
            property: 'ammunition',
            description: 'Deprecated: use ammunition from root resource.',
            type: 'object',
            nullable: true,
            deprecated: true
        ),
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
                'rounds_per_minute' => Arr::get($mode, 'RoundsPerMinute'),
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
                'alpha_total' => Arr::get($weapon, 'Damage.Alpha'),
                'maximum' => Arr::get($weapon, 'Damage.Maximum'),
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
                    'min' => Arr::get($mode, 'Spread.Min'),
                    'max' => Arr::get($mode, 'Spread.Max'),
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
