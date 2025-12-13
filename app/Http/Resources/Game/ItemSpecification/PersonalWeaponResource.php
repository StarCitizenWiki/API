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
        $data = $this->parseSpecificationData($this->resource['data'] ?? $this->resource->data ?? null);
        $stdItem = $this->extractStdItem($data);

        $weapon = Arr::get($stdItem, 'Weapon', []);
        $ammunition = Arr::get($stdItem, 'Ammunition', []);
        $magazine = Arr::get($weapon, 'Magazine', []);
        $attachments = Arr::get($weapon, 'Attachments', []);

        $impactDamage = $this->buildDamageArray(Arr::get($ammunition, 'ImpactDamage', []));
        $detonationDamage = $this->buildDamageArray(Arr::get($ammunition, 'DetonationDamage', []));
        $ammunitionResource = new AmmunitionResource($this->resource);

        return [
            'weapon_class' => Arr::get($weapon, 'WeaponClass'),
            'weapon_size' => Arr::get($weapon, 'Size'),
            'effective_range' => Arr::get($weapon, 'EffectiveRange'),
            'rate_of_fire' => Arr::get($weapon, 'RateOfFire'),
            'capacity' => Arr::get($weapon, 'Capacity'),
            'magazine' => [
                'max_ammo' => Arr::get($magazine, 'MaxAmmoCount'),
                'initial_ammo' => Arr::get($magazine, 'InitialAmmoCount'),
            ],
            'attachments' => collect($attachments)->map(
                static fn (mixed $attachment): array => [
                    'port' => Arr::get($attachment, 'Port'),
                    'class_name' => Arr::get($attachment, 'ClassName'),
                ]
            ),
            'modes' => collect(Arr::get($weapon, 'Modes', []))->map(
                static fn (mixed $mode): array => [
                    'name' => Arr::get($mode, 'Name'),
                    'label' => Arr::get($mode, 'LocalisedName'),
                    'fire_type' => Arr::get($mode, 'FireType'),
                    'rounds_per_minute' => Arr::get($mode, 'RoundsPerMinute'),
                    'ammo_per_shot' => Arr::get($mode, 'AmmoPerShot'),
                    'pellets_per_shot' => Arr::get($mode, 'PelletsPerShot'),
                    'damage_per_shot' => Arr::get($mode, 'DamagePerShot'),
                    'damage_per_second' => Arr::get($mode, 'DamagePerSecond'),
                ]
            ),
            'ammunition' => $ammunitionResource,
            'consumption' => Arr::get($weapon, 'Consumption'),
            // Backward compatibility with v2
            'class' => Arr::get($weapon, 'WeaponClass'),
            'magazine_size' => Arr::get($magazine, 'MaxAmmoCount'),
            'damage_per_shot' => $this->calculateTotalDamage(Arr::get($ammunition, 'ImpactDamage', [])),
            'rof' => Arr::get($weapon, 'RateOfFire'),
            'damages' => $impactDamage,
            'magazine_type' => null,
        ];
    }
}
