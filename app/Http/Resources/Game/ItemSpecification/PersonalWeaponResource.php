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
            property: 'magazine',
            properties: [
                new OA\Property(property: 'max_ammo', type: 'integer', example: 50, nullable: true),
                new OA\Property(property: 'initial_ammo', type: 'integer', example: 50, nullable: true),
            ],
            type: 'object',
            nullable: true
        ),
        new OA\Property(
            property: 'attachments',
            description: 'Attachment ports and optional default class names.',
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'port', type: 'string', example: 'magazine_attach', nullable: true),
                    new OA\Property(property: 'class_name', type: 'string', example: 'gmni_smg_ballistic_01_mag', nullable: true),
                ],
                type: 'object'
            ),
            nullable: true
        ),
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
            property: 'ammunition',
            ref: '#/components/schemas/ammunition',
            description: 'Projectile and damage behaviour from Item.stdItem.Ammunition.',
            nullable: true
        ),
        new OA\Property(
            property: 'consumption',
            description: 'Regen/cost values used by special weapons (e.g. extinguishers).',
            type: 'object',
            nullable: true
        ),
        // Backward compatibility (v2)
        new OA\Property(property: 'class', type: 'string', example: 'Medium', nullable: true, deprecated: true),
        new OA\Property(property: 'magazine_size', type: 'integer', example: 50, nullable: true, deprecated: true),
        new OA\Property(property: 'damage_per_shot', type: 'double', example: 11.5, nullable: true, deprecated: true),
        new OA\Property(property: 'rof', type: 'double', example: 925, nullable: true, deprecated: true),
        new OA\Property(property: 'damages', type: 'array', items: new OA\Items(ref: '#/components/schemas/weapon_damage_entry'), nullable: true, deprecated: true),
        new OA\Property(property: 'magazine_type', type: 'string', nullable: true, deprecated: true),
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

            'range' => Arr::get($weapon, 'EffectiveRange'),

            'damage_per_shot' => Arr::get($mode, 'Alpha'),

            'rpm' => Arr::get($mode, 'RoundsPerMinute'),

            'damages' => $damages,
            'modes' => $modes,

            'damage' => [
                'dps_total' => Arr::get($mode, 'Dps'),
                'alpha_total' => Arr::get($mode, 'Alpha'),
                'maximum' => Arr::get($mode, 'MaxDamagePerMagazine') === 0 ? -1 : Arr::get($mode, 'MaxDamagePerMagazine'),
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

            $this->mergeWhen(Arr::get($mode, 'Spread.Min') !== null, [
                'spread' => [
                    'min' => Arr::get($mode, 'Spread.Min'),
                    'max' => Arr::get($mode, 'Spread.Max'),
                    'first_attack' => Arr::get($mode, 'Spread.FirstAttack'),
                    'per_attack' => Arr::get($mode, 'Spread.Attack'),
                    'decay' => Arr::get($mode, 'Spread.Decay'),
                ],
                'ads_spread' => [
                    'min' => Arr::get($mode, 'AdsSpread.Min'),
                    'max' => Arr::get($mode, 'AdsSpread.Max'),
                    'first_attack' => Arr::get($mode, 'AdsSpread.FirstAttack'),
                    'per_attack' => Arr::get($mode, 'AdsSpread.Attack'),
                    'decay' => Arr::get($mode, 'AdsSpread.Decay'),
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
