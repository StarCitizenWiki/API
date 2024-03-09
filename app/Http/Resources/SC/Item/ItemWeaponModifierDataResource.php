<?php

declare(strict_types=1);

namespace App\Http\Resources\SC\Item;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'item_weapon_modifier_data_v2',
    title: 'Item Weapon Modifier Data',
    description: 'The complete SWeaponModifierComponentParams output',
    properties: [
        new OA\Property(property: 'fire_rate_multiplier', type: 'double', nullable: true),
        new OA\Property(property: 'damage_multiplier', type: 'double', nullable: true),
        new OA\Property(property: 'damage_over_time_multiplier', type: 'double', nullable: true),
        new OA\Property(property: 'projectile_speed_multiplier', type: 'double', nullable: true),
        new OA\Property(property: 'ammo_cost_multiplier', type: 'double', nullable: true),
        new OA\Property(property: 'heat_generation_multiplier', type: 'double', nullable: true),
        new OA\Property(property: 'sound_radius_multiplier', type: 'double', nullable: true),
        new OA\Property(property: 'charge_time_multiplier', type: 'double', nullable: true),
        new OA\Property(
            property: 'recoil',
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'decay_multiplier', type: 'double', nullable: true),
                    new OA\Property(property: 'end_decay_multiplier', type: 'double', nullable: true),
                    new OA\Property(property: 'fire_recoil_time_multiplier', type: 'double', nullable: true),
                    new OA\Property(property: 'fire_recoil_strength_first_multiplier', type: 'double', nullable: true),
                    new OA\Property(property: 'fire_recoil_strength_multiplier', type: 'double', nullable: true),
                    new OA\Property(property: 'angle_recoil_strength_multiplier', type: 'double', nullable: true),
                    new OA\Property(property: 'randomness_multiplier', type: 'double', nullable: true),
                    new OA\Property(property: 'randomness_back_push_multiplier', type: 'double', nullable: true),
                    new OA\Property(property: 'frontal_oscillation_rotation_multiplier', type: 'double', nullable: true),
                    new OA\Property(property: 'frontal_oscillation_strength_multiplier', type: 'double', nullable: true),
                    new OA\Property(property: 'frontal_oscillation_decay_multiplier', type: 'double', nullable: true),
                    new OA\Property(property: 'frontal_oscillation_randomness_multiplier', type: 'double', nullable: true),
                    new OA\Property(property: 'animated_recoil_multiplier', type: 'double', nullable: true),
                ]
            ), nullable: true
        ),
        new OA\Property(
            property: 'spread',
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'min_multiplier', type: 'double', nullable: true),
                    new OA\Property(property: 'max_multiplier', type: 'double', nullable: true),
                    new OA\Property(property: 'first_attack_multiplier', type: 'double', nullable: true),
                    new OA\Property(property: 'attack_multiplier', type: 'double', nullable: true),
                    new OA\Property(property: 'decay_multiplier', type: 'double', nullable: true),
                    new OA\Property(property: 'additive_modifier', type: 'double', nullable: true),
                ]
            ), nullable: true
        ),
        new OA\Property(
            property: 'aim',
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'zoom_scale', type: 'double', nullable: true),
                    new OA\Property(property: 'zoom_time_scale', type: 'double', nullable: true),
                ]
            ), nullable: true
        ),
        new OA\Property(
            property: 'slavage',
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'radius_multiplier', type: 'double', nullable: true),
                    new OA\Property(property: 'extraction_efficiency', type: 'double', nullable: true),
                    new OA\Property(property: 'speed_multiplier', type: 'double', nullable: true),
                ]
            ), nullable: true
        ),


    ],
    type: 'object'
)]
class ItemWeaponModifierDataResource extends AbstractBaseResource
{
    /**
     * Transform the resource collection into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'fire_rate_multiplier' => $this->fire_rate_multiplier,
            'damage_multiplier' => $this->damage_multiplier,
            'damage_over_time_multiplier' => $this->damage_over_time_multiplier,
            'projectile_speed_multiplier' => $this->projectile_speed_multiplier,
            'ammo_cost_multiplier' => $this->ammo_cost_multiplier,
            'heat_generation_multiplier' => $this->heat_generation_multiplier,
            'sound_radius_multiplier' => $this->sound_radius_multiplier,
            'charge_time_multiplier' => $this->charge_time_multiplier,
            'recoil' => array_filter([
                'decay_multiplier' => $this->recoil_decay_multiplier,
                'end_decay_multiplier' => $this->recoil_end_decay_multiplier,
                'fire_recoil_time_multiplier' => $this->recoil_fire_recoil_time_multiplier,
                'fire_recoil_strength_first_multiplier' => $this->recoil_fire_recoil_strength_first_multiplier,
                'fire_recoil_strength_multiplier' => $this->recoil_fire_recoil_strength_multiplier,
                'angle_recoil_strength_multiplier' => $this->recoil_angle_recoil_strength_multiplier,
                'randomness_multiplier' => $this->recoil_randomness_multiplier,
                'randomness_back_push_multiplier' => $this->recoil_randomness_back_push_multiplier,
                'frontal_oscillation_rotation_multiplier' => $this->recoil_frontal_oscillation_rotation_multiplier,
                'frontal_oscillation_strength_multiplier' => $this->recoil_frontal_oscillation_strength_multiplier,
                'frontal_oscillation_decay_multiplier' => $this->recoil_frontal_oscillation_decay_multiplier,
                'frontal_oscillation_randomness_multiplier' => $this->recoil_frontal_oscillation_randomness_multiplier,
                'animated_recoil_multiplier' => $this->recoil_animated_recoil_multiplier,
            ]),
            'spread' => array_filter([
                'min_multiplier' => $this->spread_min_multiplier,
                'max_multiplier' => $this->spread_max_multiplier,
                'first_attack_multiplier' => $this->spread_first_attack_multiplier,
                'attack_multiplier' => $this->spread_attack_multiplier,
                'decay_multiplier' => $this->spread_decay_multiplier,
                'additive_modifier' => $this->spread_additive_modifier,
            ]),
            'aim' => array_filter([
                'zoom_scale' => $this->aim_zoom_scale,
                'zoom_time_scale' => $this->aim_zoom_time_scale,
            ]),
            'salvage' => array_filter([
                'salvage_speed_multiplier' => $this->spread_salvage_speed_multiplier,
                'radius_multiplier' => $this->salvage_radius_multiplier,
                'extraction_efficiency' => $this->salvage_extraction_efficiency,
            ]),
        ];
    }
}
