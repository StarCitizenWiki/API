<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\ItemSpecification;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'personal_weapon_mode',
    title: 'Personal Weapon Mode',
    description: 'Fire mode entries as returned by the game data mapping.',
    properties: [
        new OA\Property(property: 'mode', description: 'Mode name (Modes[].Name).', type: 'string', example: 'Rapid', nullable: true),
        new OA\Property(property: 'localised', description: 'Localized label (Modes[].LocalisedName).', type: 'string', example: '[AUTO]', nullable: true),
        new OA\Property(property: 'type', description: 'Fire type (Modes[].FireType).', type: 'string', example: 'rapid', nullable: true),
        new OA\Property(property: 'rpm', description: 'Rounds per minute (Modes[].RoundsPerMinute).', type: 'double', example: 925, nullable: true),
        new OA\Property(property: 'ammo_per_shot', description: 'Ammo consumed per shot (Modes[].AmmoPerShot).', type: 'integer', example: 1, nullable: true),
        new OA\Property(property: 'pellets_per_shot', description: 'Pellets per shot (Modes[].PelletsPerShot).', type: 'integer', example: 12, nullable: true),
        new OA\Property(property: 'damage_per_second', description: 'Mode DPS as provided (Modes[].DamagePerSecond).', type: 'double', example: 0, nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'personal_weapon_damage_vector',
    title: 'Personal Weapon Damage Vector',
    description: 'Damage values broken down by damage type.',
    properties: [
        new OA\Property(property: 'physical', type: 'double', example: 11.5, nullable: true),
        new OA\Property(property: 'energy', type: 'double', example: 0, nullable: true),
        new OA\Property(property: 'distortion', type: 'double', example: 0, nullable: true),
        new OA\Property(property: 'thermal', type: 'double', example: 0, nullable: true),
        new OA\Property(property: 'biochemical', type: 'double', example: 0, nullable: true),
        new OA\Property(property: 'stun', type: 'double', example: 0, nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'personal_weapon_damage',
    title: 'Personal Weapon Damage',
    description: 'Weapon damage totals and per-type breakdowns from Weapon.Damage.',
    properties: [
        new OA\Property(property: 'dps_total', type: 'double', example: 1150.0, nullable: true),
        new OA\Property(property: 'alpha_total', type: 'double', example: 11.5, nullable: true),
        new OA\Property(property: 'maximum', description: 'Maximum damage per magazine (Damage.MaxPerMag).', type: 'double', example: 575.0, nullable: true),
        new OA\Property(property: 'dps', ref: '#/components/schemas/personal_weapon_damage_vector', nullable: true),
        new OA\Property(property: 'alpha', ref: '#/components/schemas/personal_weapon_damage_vector', nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'personal_weapon_spread',
    title: 'Personal Weapon Spread',
    description: 'Spread configuration. Only present when spread data exists in the source.',
    properties: [
        new OA\Property(property: 'minimum', type: 'double', example: 0.1, nullable: true),
        new OA\Property(property: 'maximum', type: 'double', example: 1.2, nullable: true),
        new OA\Property(property: 'first_attack', type: 'double', example: 0.2, nullable: true),
        new OA\Property(property: 'per_attack', type: 'double', example: 0.05, nullable: true),
        new OA\Property(property: 'decay', type: 'double', example: 0.3, nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'personal_weapon_charge',
    title: 'Personal Weapon Charge',
    description: 'Charge timings. Only present when charge data exists in the source.',
    properties: [
        new OA\Property(property: 'time', type: 'double', example: 1.0, nullable: true),
        new OA\Property(property: 'overcharge_time', type: 'double', example: 0.5, nullable: true),
        new OA\Property(property: 'overcharged_time', type: 'double', example: 0.5, nullable: true),
        new OA\Property(property: 'cooldown_time', type: 'double', example: 1.0, nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'personal_weapon_charge_modifier',
    title: 'Personal Weapon Charge Modifier',
    description: 'Charge modifiers. Only present when charge data exists in the source.',
    properties: [
        new OA\Property(property: 'damage', type: 'double', example: 1.0, nullable: true),
        new OA\Property(property: 'fire_rate', type: 'double', example: 1.0, nullable: true),
        new OA\Property(property: 'ammo_speed', type: 'double', example: 1.0, nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'personal_weapon',
    title: 'Personal Weapon',
    description: 'FPS weapon specification sourced from Item.stdItem.Weapon and Item.stdItem.Ammunition for WeaponPersonal items. Legacy v2 fields are preserved and marked deprecated.',
    properties: [
        new OA\Property(property: 'class', description: 'Class as provided by DescriptionData.Class.', type: 'string', example: 'Medium', nullable: true),
        new OA\Property(property: 'type', description: 'Type as provided by DescriptionData.Item Type.', type: 'string', example: 'Rifle', nullable: true),

        new OA\Property(
            property: 'magazine_type',
            description: 'Deprecated: legacy field, currently returned as an empty string; do not use.',
            type: 'string',
            nullable: true,
            deprecated: true
        ),
        new OA\Property(
            property: 'magazine_size',
            description: 'Deprecated: use `capacity`.',
            type: 'integer',
            example: 50,
            nullable: true,
            deprecated: true
        ),

        new OA\Property(
            property: 'effective_range',
            description: 'Deprecated: use `range`.',
            type: 'double',
            example: 950,
            nullable: true,
            deprecated: true
        ),

        new OA\Property(property: 'capacity', description: 'Weapon capacity (Weapon.Capacity).', type: 'integer', example: 50, nullable: true),
        new OA\Property(property: 'range', description: 'Effective range in meters (Weapon.EffectiveRange).', type: 'double', example: 950, nullable: true),

        new OA\Property(
            property: 'damage_per_shot',
            description: 'Deprecated: use `damage.alpha_total` (or per-type `damage.alpha.*`) instead.',
            type: 'double',
            example: 11.5,
            nullable: true,
            deprecated: true
        ),
        new OA\Property(property: 'pellets_per_shot', description: 'Pellets per shot (Weapon.PelletsPerShot).', type: 'integer', example: 12, nullable: true),

        new OA\Property(
            property: 'rof',
            description: 'Deprecated: use `rpm`.',
            type: 'double',
            example: 925,
            nullable: true,
            deprecated: true
        ),
        new OA\Property(property: 'rpm', description: 'Rounds per minute for the first mode (Modes[0].RoundsPerMinute).', type: 'double', example: 925, nullable: true),

        new OA\Property(
            property: 'damages',
            description: 'Deprecated: legacy ammunition-derived entries. Prefer `damage` for weapon damage totals/breakdowns.',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/weapon_damage_entry'),
            nullable: true,
            deprecated: true
        ),
        new OA\Property(
            property: 'modes',
            description: 'Fire modes returned from Weapon.Modes.',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/personal_weapon_mode'),
            nullable: true
        ),

        new OA\Property(property: 'fire_mode', description: 'Weapon fire mode (Weapon.FireMode).', type: 'string', example: 'Auto', nullable: true),

        new OA\Property(property: 'damage', ref: '#/components/schemas/personal_weapon_damage'),

        new OA\Property(property: 'spread', ref: '#/components/schemas/personal_weapon_spread', nullable: true),
        new OA\Property(property: 'ads_spread', ref: '#/components/schemas/personal_weapon_spread', nullable: true),

        new OA\Property(property: 'charge', ref: '#/components/schemas/personal_weapon_charge', nullable: true),
        new OA\Property(property: 'charge_modifier', ref: '#/components/schemas/personal_weapon_charge_modifier', nullable: true),

        new OA\Property(
            property: 'ammunition',
            description: 'Deprecated: use the root-level ammunition resource (outside this specification payload) where available.',
            type: 'object',
            nullable: true,
            deprecated: true
        ),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'weapon_damage_entry',
    title: 'Weapon Damage Entry',
    description: 'Legacy ammo-derived damage entry.',
    properties: [
        new OA\Property(property: 'type', description: 'Damage context.', type: 'string', example: 'impact', nullable: true),
        new OA\Property(property: 'name', description: 'Damage type name.', type: 'string', example: 'physical', nullable: true),
        new OA\Property(property: 'damage', type: 'double', example: 11.5, nullable: true),
    ],
    type: 'object'
)]
class PersonalWeaponResource extends AbstractItemSpecificationResource
{
    public function toArray(Request $request): array
    {
        $ammo = $this->extractFromStdItem($this->resource, 'Ammunition');
        $weapon = $this->extractFromStdItem($this->resource, 'Weapon');
        $mode = Arr::get($weapon, 'Modes.0');
        $damage = Arr::get($weapon, 'Damage');

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
                'ammo_per_shot' => Arr::get($mode, 'AmmoPerShot'),
                'pellets_per_shot' => Arr::get($mode, 'PelletsPerShot'),
                'damage_per_second' => Arr::get($mode, 'DamagePerSecond'),
            ])
            ->values()
            ->toArray();

        return [
            'class' => $this->extractFromStdItem($this->resource, 'DescriptionData.Class'),
            'type' => $this->extractFromStdItem($this->resource, 'DescriptionData.Item Type'),

            // deprecated
            'magazine_type' => '',
            // deprecated
            'magazine_size' => Arr::get($weapon, 'Capacity'),

            // deprecated
            'effective_range' => Arr::get($weapon, 'EffectiveRange'),

            'capacity' => Arr::get($weapon, 'Capacity'),

            'range' => Arr::get($weapon, 'EffectiveRange'),

            'damage_per_shot' => Arr::get($mode, 'Alpha'),
            'pellets_per_shot' => Arr::get($weapon, 'PelletsPerShot'),

            // deprecated
            'rof' => Arr::get($mode, 'RoundsPerMinute'),

            'rpm' => Arr::get($mode, 'RoundsPerMinute'),

            'damages' => $damages,
            'modes' => $modes,

            'fire_mode' => Arr::get($weapon, 'FireMode'),

            'damage' => [
                'dps_total' => Arr::get($damage, 'DpsTotal'),
                'alpha_total' => Arr::get($damage, 'AlphaTotal'),
                'maximum' => Arr::get($damage, 'MaxPerMag'),
                'dps' => [
                    'physical' => Arr::get($weapon, 'Damage.Dps.Physical'),
                    'energy' => Arr::get($weapon, 'Damage.Dps.Energy'),
                    'distortion' => Arr::get($weapon, 'Damage.Dps.Distortion'),
                    'thermal' => Arr::get($weapon, 'Damage.Dps.Thermal'),
                    'biochemical' => Arr::get($weapon, 'Damage.Dps.Biochemical'),
                    'stun' => Arr::get($weapon, 'Damage.Dps.Stun'),
                ],
                'alpha' => [
                    'physical' => Arr::get($weapon, 'Damage.Alpha.Physical'),
                    'energy' => Arr::get($weapon, 'Damage.Alpha.Energy'),
                    'distortion' => Arr::get($weapon, 'Damage.Alpha.Distortion'),
                    'thermal' => Arr::get($weapon, 'Damage.Alpha.Thermal'),
                    'biochemical' => Arr::get($weapon, 'Damage.Alpha.Biochemical'),
                    'stun' => Arr::get($weapon, 'Damage.Alpha.Stun'),
                ],
            ],

            $this->mergeWhen(Arr::get($mode, 'Spread.Minimum') !== null, [
                'spread' => [
                    'minimum' => Arr::get($weapon, 'Spread.Minimum'),
                    'maximum' => Arr::get($weapon, 'Spread.Maximum'),
                    'first_attack' => Arr::get($weapon, 'Spread.FirstAttack'),
                    'per_attack' => Arr::get($weapon, 'Spread.Attack'),
                    'decay' => Arr::get($weapon, 'Spread.Decay'),
                ],
                'ads_spread' => [
                    'minimum' => Arr::get($weapon, 'AdsSpread.Minimum') == 0 ? null : Arr::get($weapon, 'AdsSpread.Min'),
                    'maximum' => Arr::get($weapon, 'AdsSpread.Maximum') == 0 ? null : Arr::get($weapon, 'AdsSpread.Max'),
                    'first_attack' => Arr::get($weapon, 'AdsSpread.FirstAttack') == 0 ? null : Arr::get($weapon, 'AdsSpread.FirstAttack'),
                    'per_attack' => Arr::get($weapon, 'AdsSpread.Attack') == 0 ? null : Arr::get($weapon, 'AdsSpread.Attack'),
                    'decay' => Arr::get($weapon, 'AdsSpread.Decay') == 0 ? null : Arr::get($weapon, 'AdsSpread.Decay'),
                ],
            ]),

            $this->mergeWhen(Arr::get($mode, 'Charge') !== null, [
                'charge' => [
                    'time' => Arr::get($weapon, 'Charge.ChargeTime'),
                    'overcharge_time' => Arr::get($weapon, 'Charge.OverchargeTime'),
                    'overcharged_time' => Arr::get($weapon, 'Charge.OverchargedTime'),
                    'cooldown_time' => Arr::get($weapon, 'Charge.CooldownTime'),
                ],
                'charge_modifier' => [
                    'damage' => Arr::get($weapon, 'ChargeModifier.Damage'),
                    'fire_rate' => Arr::get($weapon, 'ChargeModifier.FireRate'),
                    'ammo_speed' => Arr::get($weapon, 'ChargeModifier.AmmoSpeed'),
                ],
            ]),

            'ammunition' => new AmmunitionResource($this->resource),
        ];
    }
}
