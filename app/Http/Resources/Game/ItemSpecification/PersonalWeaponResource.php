<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\ItemSpecification;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'personal_weapon',
    title: 'Personal Weapon',
    description: 'FPS weapon specification sourced from Item.stdItem.Weapon and Item.stdItem.Ammunition for WeaponPersonal items. Focuses on player-relevant stats such as range, fire rates, magazine capacity, modes, and projectile behaviour. Legacy v2 fields are preserved and marked deprecated.',
    properties: [
        new OA\Property(property: 'weapon_class', type: 'string', example: 'Medium', nullable: true),
        new OA\Property(property: 'weapon_size', description: 'Weapon.Size from Item.stdItem.Weapon, distinct from the general item size.', type: 'integer', example: 2, nullable: true),
        new OA\Property(property: 'effective_range', type: 'double', example: 950, nullable: true),
        new OA\Property(property: 'rate_of_fire', description: 'Overall rate of fire in rounds per minute.', type: 'double', example: 925, nullable: true),
        new OA\Property(property: 'capacity', description: 'Weapon-level capacity field when provided.', type: 'integer', example: 50, nullable: true),

        new OA\Property(
            property: 'modes',
            description: 'Fire modes as provided by game data; values are not derived.',
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Rapid', nullable: true),
                    new OA\Property(property: 'label', type: 'string', example: '[AUTO]', nullable: true),
                    new OA\Property(property: 'fire_type', type: 'string', example: 'rapid', nullable: true),
                    new OA\Property(property: 'rounds_per_minute', type: 'double', example: 925, nullable: true),
                    new OA\Property(property: 'ammo_per_shot', type: 'integer', example: 1, nullable: true),
                    new OA\Property(property: 'pellets_per_shot', type: 'integer', example: 12, nullable: true),
                    new OA\Property(property: 'damage_per_shot', type: 'double', example: 0, nullable: true),
                    new OA\Property(property: 'damage_per_second', type: 'double', example: 0, nullable: true),
                ],
                type: 'object'
            ),
            nullable: true
        ),
        new OA\Property(
            property: 'consumption',
            description: 'Regen/cost values used by special weapons (e.g. extinguishers).',
            type: 'object',
            nullable: true
        ),

        new OA\Property(property: 'class', type: 'string', example: 'Medium', nullable: true, deprecated: true),
        new OA\Property(property: 'magazine_size', type: 'integer', example: 50, nullable: true, deprecated: true, description: 'Deprecated: Use capacity instead'),
        new OA\Property(property: 'damage_per_shot', type: 'double', example: 11.5, nullable: true, deprecated: true, description: 'Deprecated: Use damages.alpha_total'),
        new OA\Property(property: 'rof', type: 'double', example: 925, nullable: true, deprecated: true, description: 'Deprecated: Use rpm instead'),
        new OA\Property(property: 'rpm', type: 'double', example: 925, nullable: true),
        new OA\Property(property: 'damages', type: 'array', items: new OA\Items(ref: '#/components/schemas/weapon_damage_entry'), nullable: true, deprecated: true),
        new OA\Property(property: 'magazine_type', type: 'string', nullable: true, deprecated: true),
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
#[OA\Schema(
    schema: 'weapon_damage_entry',
    title: 'Weapon Damage Entry',
    properties: [
        new OA\Property(property: 'type', type: 'string', example: 'Physical'),
        new OA\Property(property: 'name', type: 'string', example: 'Physical'),
        new OA\Property(property: 'damage', type: 'double', example: 11.5),
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
                    'min' => Arr::get($weapon, 'Spread.Minimum'),
                    'max' => Arr::get($weapon, 'Spread.Maximum'),
                    'first_attack' => Arr::get($weapon, 'Spread.FirstAttack'),
                    'per_attack' => Arr::get($weapon, 'Spread.Attack'),
                    'decay' => Arr::get($weapon, 'Spread.Decay'),
                ],
                'ads_spread' => [
                    'min' => Arr::get($weapon, 'AdsSpread.Minimum') == 0 ? null : Arr::get($weapon, 'AdsSpread.Min'),
                    'max' => Arr::get($weapon, 'AdsSpread.Maximum') == 0 ? null : Arr::get($weapon, 'AdsSpread.Max'),
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
