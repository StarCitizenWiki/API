<?php

namespace App\Models\SC\Item;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemWeaponModifierData extends Model
{
    use HasFactory;

    protected $table = 'sc_item_weapon_modifier_data';

    protected $fillable = [
        'item_uuid',
        'fire_rate_multiplier',
        'damage_multiplier',
        'damage_over_time_multiplier',
        'projectile_speed_multiplier',
        'ammo_cost_multiplier',
        'heat_generation_multiplier',
        'sound_radius_multiplier',
        'charge_time_multiplier',

        'recoil_decay_multiplier',
        'recoil_end_decay_multiplier',
        'recoil_fire_recoil_time_multiplier',
        'recoil_fire_recoil_strength_first_multiplier',
        'recoil_fire_recoil_strength_multiplier',
        'recoil_angle_recoil_strength_multiplier',
        'recoil_randomness_multiplier',
        'recoil_randomness_back_push_multiplier',
        'recoil_frontal_oscillation_rotation_multiplier',
        'recoil_frontal_oscillation_strength_multiplier',
        'recoil_frontal_oscillation_decay_multiplier',
        'recoil_frontal_oscillation_randomness_multiplier',
        'recoil_animated_recoil_multiplier',

        'spread_min_multiplier',
        'spread_max_multiplier',
        'spread_first_attack_multiplier',
        'spread_attack_multiplier',
        'spread_decay_multiplier',
        'spread_additive_modifier',

        'aim_zoom_scale',
        'aim_zoom_time_scale',

        'salvage_speed_multiplier',
        'salvage_radius_multiplier',
        'salvage_extraction_efficiency',
    ];

    protected $casts = [
        'fire_rate_multiplier' => 'double',
        'damage_multiplier' => 'double',
        'damage_over_time_multiplier' => 'double',
        'projectile_speed_multiplier' => 'double',
        'ammo_cost_multiplier' => 'double',
        'heat_generation_multiplier' => 'double',
        'sound_radius_multiplier' => 'double',
        'charge_time_multiplier' => 'double',

        'recoil_decay_multiplier' => 'double',
        'recoil_end_decay_multiplier' => 'double',
        'recoil_fire_recoil_time_multiplier' => 'double',
        'recoil_fire_recoil_strength_first_multiplier' => 'double',
        'recoil_fire_recoil_strength_multiplier' => 'double',
        'recoil_angle_recoil_strength_multiplier' => 'double',
        'recoil_randomness_multiplier' => 'double',
        'recoil_randomness_back_push_multiplier' => 'double',
        'recoil_frontal_oscillation_rotation_multiplier' => 'double',
        'recoil_frontal_oscillation_strength_multiplier' => 'double',
        'recoil_frontal_oscillation_decay_multiplier' => 'double',
        'recoil_frontal_oscillation_randomness_multiplier' => 'double',
        'recoil_animated_recoil_multiplier' => 'double',

        'spread_min_multiplier' => 'double',
        'spread_max_multiplier' => 'double',
        'spread_first_attack_multiplier' => 'double',
        'spread_attack_multiplier' => 'double',
        'spread_decay_multiplier' => 'double',
        'spread_additive_modifier' => 'double',

        'aim_zoom_scale' => 'double',
        'aim_zoom_time_scale' => 'double',

        'salvage_speed_multiplier' => 'double',
        'salvage_radius_multiplier' => 'double',
        'salvage_extraction_efficiency' => 'double',
    ];
}
