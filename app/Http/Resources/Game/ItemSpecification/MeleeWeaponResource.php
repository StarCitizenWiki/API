<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\ItemSpecification;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'melee_weapon',
    title: 'Melee Weapon',
    description: 'Melee weapon specification sourced from Item.stdItem.MeleeWeapon (or meleeWeapon). Captures player-relevant melee flags and attack configs without additional processing.',
    properties: [
        new OA\Property(property: 'can_be_used_for_take_down', type: 'boolean', example: true, nullable: true),
        new OA\Property(property: 'can_block', type: 'boolean', example: true, nullable: true),
        new OA\Property(property: 'can_be_used_in_prone', type: 'boolean', example: false, nullable: true),
        new OA\Property(property: 'can_dodge', type: 'boolean', example: true, nullable: true),
        new OA\Property(property: 'stance_transition_melee_delay', type: 'double', example: 0.6, nullable: true),
        new OA\Property(property: 'melee_combat_config', description: 'UUID reference to the melee combat config record.', type: 'string', example: 'da909c53-195a-4289-8466-ecb4c418f930', nullable: true),
        new OA\Property(
            property: 'attack_modes',
            description: 'Attack configurations as provided by the game data.',
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'action_category', type: 'string', example: 'BladeSlash', nullable: true),
                    new OA\Property(property: 'stun_recovery_modifier', type: 'double', example: 0, nullable: true),
                    new OA\Property(property: 'block_stun_reduction_modifier', type: 'double', example: 0, nullable: true),
                    new OA\Property(property: 'block_stun_stamina_modifier', type: 'double', example: 0, nullable: true),
                    new OA\Property(property: 'attack_impulse', type: 'double', example: 20, nullable: true),
                    new OA\Property(property: 'ignore_body_part_impulse_scale', type: 'boolean', example: false, nullable: true),
                    new OA\Property(property: 'force_knockdown', type: 'string', example: 'None', nullable: true),
                    new OA\Property(
                        property: 'damage',
                        properties: [
                            new OA\Property(property: 'physical', type: 'double', example: 30, nullable: true),
                            new OA\Property(property: 'energy', type: 'double', example: 0, nullable: true),
                            new OA\Property(property: 'distortion', type: 'double', example: 0, nullable: true),
                            new OA\Property(property: 'thermal', type: 'double', example: 0, nullable: true),
                            new OA\Property(property: 'biochemical', type: 'double', example: 0, nullable: true),
                            new OA\Property(property: 'stun', type: 'double', example: 0, nullable: true),
                        ],
                        type: 'object',
                        nullable: true
                    ),
                    new OA\Property(property: 'damage_total', type: 'double', example: 30, nullable: true),
                ],
                type: 'object'
            ),
            nullable: true
        ),
        // Legacy names for backward compatibility (v2)
        new OA\Property(
            property: 'attack_modes_legacy',
            description: 'Deprecated: legacy melee combat config format.',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/melee_combat_config_v2'),
            deprecated: true,
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
            'melee_combat_config' => Arr::get($melee, 'MeleeCombatConfig', Arr::get($melee, 'meleeCombatConfig')),
            'attack_modes' => collect(is_array($attackConfigs) ? $attackConfigs : [$attackConfigs])->map(
                static function (mixed $attack): array {
                    $damage = Arr::get($attack, 'Damage', []);

                    return [
                        'action_category' => Arr::get($attack, 'ActionCategory'),
                        'stun_recovery_modifier' => Arr::get($attack, 'StunRecoveryModifier'),
                        'block_stun_reduction_modifier' => Arr::get($attack, 'BlockStunReductionModifier'),
                        'block_stun_stamina_modifier' => Arr::get($attack, 'BlockStunStaminaModifier'),
                        'attack_impulse' => Arr::get($attack, 'AttackImpulse'),
                        'ignore_body_part_impulse_scale' => Arr::get($attack, 'IgnoreBodyPartImpulseScale'),
                        'force_knockdown' => Arr::get($attack, 'ForceKnockdown'),
                        'damage' => [
                            'physical' => Arr::get($damage, 'Physical'),
                            'energy' => Arr::get($damage, 'Energy'),
                            'distortion' => Arr::get($damage, 'Distortion'),
                            'thermal' => Arr::get($damage, 'Thermal'),
                            'biochemical' => Arr::get($damage, 'Biochemical'),
                            'stun' => Arr::get($damage, 'Stun'),
                        ],
                        'damage_total' => Arr::get($attack, 'DamageTotal'),
                    ];
                }
            ),
            // Legacy compatibility: expose raw MeleeCombatConfigResource collection when available
            'attack_modes_legacy' => $this->buildLegacyCombatConfig($melee),
        ];
    }

    private function buildLegacyCombatConfig(array $melee): mixed
    {
        $attackConfigs = Arr::get($melee, 'AttackConfig');

        if ($attackConfigs === null) {
            return null;
        }

        return collect(is_array($attackConfigs) ? $attackConfigs : [$attackConfigs])->map(
            function (mixed $attack): array {
                $damage = Arr::get($attack, 'Damage', []);

                return [
                    'category' => Arr::get($attack, 'ActionCategory'),
                    'damage' => Arr::get($attack, 'DamageTotal'),
                    'stun_recovery_modifier' => Arr::get($attack, 'StunRecoveryModifier'),
                    'block_stun_reduction_modifier' => Arr::get($attack, 'BlockStunReductionModifier'),
                    'block_stun_stamina_modifier' => Arr::get($attack, 'BlockStunStaminaModifier'),
                    'attack_impulse' => Arr::get($attack, 'AttackImpulse'),
                    'ignore_body_part_impulse_scale' => Arr::get($attack, 'IgnoreBodyPartImpulseScale'),
                    'fullbody_animation' => null,
                    'damages' => $this->buildDamageArray($damage),
                ];
            }
        );
    }
}
