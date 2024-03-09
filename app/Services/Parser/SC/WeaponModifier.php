<?php

declare(strict_types=1);

namespace App\Services\Parser\SC;

use Illuminate\Support\Arr;

final class WeaponModifier extends AbstractCommodityItem
{
    public function getData(): ?array
    {
        $attachDef = $this->getAttachDef();
        $modifier = $this->get('SWeaponModifierComponentParams.modifier.weaponStats');

        if ($attachDef === null || $modifier === null) {
            return null;
        }

        return [
            'uuid' => $this->getUUID(),
            'fire_rate_multiplier' => Arr::get($modifier, 'fireRateMultiplier'),
            'damage_multiplier' => Arr::get($modifier, 'damageMultiplier'),
            'damage_over_time_multiplier' => Arr::get($modifier, 'damageOverTimeMultiplier'),
            'projectile_speed_multiplier' => Arr::get($modifier, 'projectileSpeedMultiplier'),
            'ammo_cost_multiplier' => Arr::get($modifier, 'ammoCostMultiplier'),
            'heat_generation_multiplier' => Arr::get($modifier, 'heatGenerationMultiplier'),
            'sound_radius_multiplier' => Arr::get($modifier, 'soundRadiusMultiplier'),
            'charge_time_multiplier' => Arr::get($modifier, 'chargeTimeMultiplier'),

            'recoil_decay_multiplier' => Arr::get($modifier, 'recoilModifier.decayMultiplier'),
            'recoil_end_decay_multiplier' => Arr::get($modifier, 'recoilModifier.endDecayMultiplier'),
            'recoil_fire_recoil_time_multiplier' => Arr::get($modifier, 'recoilModifier.fireRecoilTimeMultiplier'),
            'recoil_fire_recoil_strength_first_multiplier' => Arr::get($modifier, 'recoilModifier.fireRecoilStrengthFirstMultiplier'),
            'recoil_fire_recoil_strength_multiplier' => Arr::get($modifier, 'recoilModifier.fireRecoilStrengthMultiplier'),
            'recoil_angle_recoil_strength_multiplier' => Arr::get($modifier, 'recoilModifier.angleRecoilStrengthMultiplier'),
            'recoil_randomness_multiplier' => Arr::get($modifier, 'recoilModifier.randomnessMultiplier'),
            'recoil_randomness_back_push_multiplier' => Arr::get($modifier, 'recoilModifier.randomnessBackPushMultiplier'),
            'recoil_frontal_oscillation_rotation_multiplier' => Arr::get($modifier, 'recoilModifier.frontalOscillationRotationMultiplier'),
            'recoil_frontal_oscillation_strength_multiplier' => Arr::get($modifier, 'recoilModifier.frontalOscillationStrengthMultiplier'),
            'recoil_frontal_oscillation_decay_multiplier' => Arr::get($modifier, 'recoilModifier.frontalOscillationDecayMultiplier'),
            'recoil_frontal_oscillation_randomness_multiplier' => Arr::get($modifier, 'recoilModifier.frontalOscillationRandomnessMultiplier'),
            'recoil_animated_recoil_multiplier' => Arr::get($modifier, 'recoilModifier.animatedRecoilMultiplier'),

            'spread_min_multiplier' => Arr::get($modifier, 'spreadModifier.minMultiplier'),
            'spread_max_multiplier' => Arr::get($modifier, 'spreadModifier.maxMultiplier'),
            'spread_first_attack_multiplier' => Arr::get($modifier, 'spreadModifier.firstAttackMultiplier'),
            'spread_attack_multiplier' => Arr::get($modifier, 'spreadModifier.attackMultiplier'),
            'spread_decay_multiplier' => Arr::get($modifier, 'spreadModifier.decayMultiplier'),
            'spread_additive_modifier' => Arr::get($modifier, 'spreadModifier.additiveModifier'),
            'aim_zoom_scale' => Arr::get($modifier, 'aimModifier.zoomScale'),
            'aim_zoom_time_scale' => Arr::get($modifier, 'aimModifier.zoomTimeScale'),

            'salvage_speed_multiplier' => Arr::get($modifier, 'salvageModifier.salvageSpeedMultiplier'),
            'salvage_radius_multiplier' => Arr::get($modifier, 'salvageModifier.radiusMultiplier'),
            'salvage_extraction_efficiency' => Arr::get($modifier, 'salvageModifier.extractionEfficiency'),
        ];
    }
}
