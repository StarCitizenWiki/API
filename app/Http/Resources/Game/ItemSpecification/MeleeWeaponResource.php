<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\ItemSpecification;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'melee_weapon_attack_damages',
    title: 'Melee Weapon Attack Damages',
    description: 'Damage breakdown for a melee attack mode.',
    properties: [
        new OA\Property(property: 'physical', description: 'Physical damage.', type: 'double', example: 30, nullable: true),
        new OA\Property(property: 'energy', description: 'Energy damage.', type: 'double', example: 0, nullable: true),
        new OA\Property(property: 'distortion', description: 'Distortion damage.', type: 'double', example: 0, nullable: true),
        new OA\Property(property: 'thermal', description: 'Thermal damage.', type: 'double', example: 0, nullable: true),
        new OA\Property(property: 'biochemical', description: 'Biochemical damage.', type: 'double', example: 0, nullable: true),
        new OA\Property(property: 'stun', description: 'Stun damage.', type: 'double', example: 0, nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'melee_weapon_attack_mode',
    title: 'Melee Weapon Attack Mode',
    description: 'Single attack configuration as returned by the resource.',
    properties: [
        new OA\Property(property: 'category', description: 'Attack animation category (e.g., BladeSlash, BladeStab, SyringeStab).', type: 'string', example: 'BladeSlash', nullable: true),
        new OA\Property(property: 'damage', description: 'Total damage value (DamageTotal).', type: 'double', example: 30, nullable: true),

        new OA\Property(property: 'stun_recovery_modifier', description: 'Modifier applied to stun recovery time.', type: 'double', example: 0, nullable: true),
        new OA\Property(property: 'block_stun_reduction_modifier', description: 'Modifier reducing stun duration when blocking.', type: 'double', example: 0, nullable: true),
        new OA\Property(property: 'block_stun_stamina_modifier', description: 'Modifier applied to stamina cost of blocking a stun.', type: 'double', example: 0, nullable: true),

        new OA\Property(property: 'attack_impulse', description: 'Physical impulse applied to the target on hit.', type: 'double', example: 20, nullable: true),
        new OA\Property(property: 'ignore_body_part_impulse_scale', description: 'Whether impulse ignores body-part-specific scaling.', type: 'boolean', example: false, nullable: true),
        new OA\Property(property: 'force_knockdown', description: 'Knockdown behavior (e.g., None).', type: 'string', example: 'None', nullable: true),

        new OA\Property(
            property: 'damages',
            ref: '#/components/schemas/melee_weapon_attack_damages',
            description: 'Damage breakdown by type.'
        ),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'melee_weapon',
    title: 'Melee Weapon',
    description: 'Melee weapon specification sourced from Item.stdItem.MeleeWeapon (or meleeWeapon).',
    properties: [
        new OA\Property(property: 'can_be_used_for_take_down', description: 'Whether this weapon can be used for takedown attacks.', type: 'boolean', example: true, nullable: true),
        new OA\Property(property: 'can_block', description: 'Whether the wielder can block incoming attacks.', type: 'boolean', example: true, nullable: true),
        new OA\Property(property: 'can_be_used_in_prone', description: 'Whether the weapon can be used while prone.', type: 'boolean', example: false, nullable: true),
        new OA\Property(property: 'can_dodge', description: 'Whether the wielder can dodge while equipped.', type: 'boolean', example: true, nullable: true),
        new OA\Property(property: 'stance_transition_melee_delay', description: 'Delay in seconds when transitioning to a melee stance.', type: 'double', example: 0.6, nullable: true, x: ['suffix' => ' s']),
        new OA\Property(
            property: 'melee_combat_config',
            description: 'UUID reference to the melee combat config record.',
            type: 'string',
            example: 'da909c53-195a-4289-8466-ecb4c418f930',
            nullable: true
        ),
        new OA\Property(
            property: 'attack_modes',
            description: 'Attack configurations as returned by the resource.',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/melee_weapon_attack_mode'),
            nullable: true
        ),
    ],
    type: 'object'
)]
class MeleeWeaponResource extends AbstractItemSpecificationResource
{
    public function toArray(Request $request): array
    {
        $data = $this->parseSpecificationData($this->resource['data'] ?? $this->resource->data ?? null);
        $stdItem = $this->extractStdItem($data);

        $melee = Arr::get($stdItem, 'MeleeWeapon', Arr::get($stdItem, 'meleeWeapon', []));
        $attackConfigs = Arr::get($melee, 'AttackConfig', []);

        return [
            'can_be_used_for_take_down' => Arr::get($melee, 'CanBeUsedForTakeDown', Arr::get($melee, 'canBeUsedForTakeDown')),
            'can_block' => Arr::get($melee, 'CanBlock', Arr::get($melee, 'canBlock')),
            'can_be_used_in_prone' => Arr::get($melee, 'CanBeUsedInProne', Arr::get($melee, 'canBeUsedInProne')),
            'can_dodge' => Arr::get($melee, 'CanDodge', Arr::get($melee, 'canDodge')),
            'stance_transition_melee_delay' => Arr::get($melee, 'StanceTransitionMeleeDelay', Arr::get($melee, 'stanceTransitionMeleeDelay')),
            'melee_combat_config' => Arr::get($melee, 'MeleeCombatConfig'),
            'attack_modes' => collect(is_array($attackConfigs) ? $attackConfigs : [$attackConfigs])->map(
                static function (mixed $attack): array {
                    $damage = Arr::get($attack, 'Damage', []);

                    return [
                        'category' => Arr::get($attack, 'ActionCategory'),
                        'damage' => Arr::get($attack, 'DamageTotal'),
                        'stun_recovery_modifier' => Arr::get($attack, 'StunRecoveryModifier'),
                        'block_stun_reduction_modifier' => Arr::get($attack, 'BlockStunReductionModifier'),
                        'block_stun_stamina_modifier' => Arr::get($attack, 'BlockStunStaminaModifier'),
                        'attack_impulse' => Arr::get($attack, 'AttackImpulse'),
                        'ignore_body_part_impulse_scale' => Arr::get($attack, 'IgnoreBodyPartImpulseScale'),
                        'force_knockdown' => Arr::get($attack, 'ForceKnockdown'),
                        'damages' => [
                            'physical' => Arr::get($damage, 'Physical'),
                            'energy' => Arr::get($damage, 'Energy'),
                            'distortion' => Arr::get($damage, 'Distortion'),
                            'thermal' => Arr::get($damage, 'Thermal'),
                            'biochemical' => Arr::get($damage, 'Biochemical'),
                            'stun' => Arr::get($damage, 'Stun'),
                        ],
                    ];
                }
            ),
        ];
    }
}
