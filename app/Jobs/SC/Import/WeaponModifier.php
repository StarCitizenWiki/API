<?php

declare(strict_types=1);

namespace App\Jobs\SC\Import;

use App\Models\SC\Item\ItemWeaponModifierData;
use App\Services\Parser\SC\Labels;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use JsonException;

class WeaponModifier implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private string $filePath;

    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $labels = (new Labels())->getData();

        try {
            $parser = new \App\Services\Parser\SC\WeaponModifier($this->filePath, $labels);
        } catch (FileNotFoundException|JsonException $e) {
            $this->fail($e);

            return;
        }
        $data = $parser->getData();

        if ($data === null) {
            return;
        }

        /** @var ItemWeaponModifierData $model */
        ItemWeaponModifierData::updateOrCreate([
            'item_uuid' => $data['uuid'],
        ], [
            'fire_rate_multiplier' => $data['fire_rate_multiplier'] ?? null,
            'damage_multiplier' => $data['damage_multiplier'] ?? null,
            'damage_over_time_multiplier' => $data['damage_over_time_multiplier'] ?? null,
            'projectile_speed_multiplier' => $data['projectile_speed_multiplier'] ?? null,
            'ammo_cost_multiplier' => $data['ammo_cost_multiplier'] ?? null,
            'heat_generation_multiplier' => $data['heat_generation_multiplier'] ?? null,
            'sound_radius_multiplier' => $data['sound_radius_multiplier'] ?? null,
            'charge_time_multiplier' => $data['charge_time_multiplier'] ?? null,

            'recoil_decay_multiplier' => $data['recoil_decay_multiplier'] ?? null,
            'recoil_end_decay_multiplier' => $data['recoil_end_decay_multiplier'] ?? null,
            'recoil_fire_recoil_time_multiplier' => $data['recoil_fire_recoil_time_multiplier'] ?? null,
            'recoil_fire_recoil_strength_first_multiplier' => $data['recoil_fire_recoil_strength_first_multiplier'] ?? null,
            'recoil_fire_recoil_strength_multiplier' => $data['recoil_fire_recoil_strength_multiplier'] ?? null,
            'recoil_angle_recoil_strength_multiplier' => $data['recoil_angle_recoil_strength_multiplier'] ?? null,
            'recoil_randomness_multiplier' => $data['recoil_randomness_multiplier'] ?? null,
            'recoil_randomness_back_push_multiplier' => $data['recoil_randomness_back_push_multiplier'] ?? null,
            'recoil_frontal_oscillation_rotation_multiplier' => $data['recoil_frontal_oscillation_rotation_multiplier'] ?? null,
            'recoil_frontal_oscillation_strength_multiplier' => $data['recoil_frontal_oscillation_strength_multiplier'] ?? null,
            'recoil_frontal_oscillation_decay_multiplier' => $data['recoil_frontal_oscillation_decay_multiplier'] ?? null,
            'recoil_frontal_oscillation_randomness_multiplier' => $data['recoil_frontal_oscillation_randomness_multiplier'] ?? null,
            'recoil_animated_recoil_multiplier' => $data['recoil_animated_recoil_multiplier'] ?? null,

            'spread_min_multiplier' => $data['spread_min_multiplier'] ?? null,
            'spread_max_multiplier' => $data['spread_max_multiplier'] ?? null,
            'spread_first_attack_multiplier' => $data['spread_first_attack_multiplier'] ?? null,
            'spread_attack_multiplier' => $data['spread_attack_multiplier'] ?? null,
            'spread_decay_multiplier' => $data['spread_decay_multiplier'] ?? null,
            'spread_additive_modifier' => $data['spread_additive_modifier'] ?? null,

            'aim_zoom_scale' => $data['aim_zoom_scale'] ?? null,
            'aim_zoom_time_scale' => $data['aim_zoom_time_scale'] ?? null,

            'salvage_speed_multiplier' => $data['salvage_speed_multiplier'] ?? null,
            'salvage_radius_multiplier' => $data['salvage_radius_multiplier'] ?? null,
            'salvage_extraction_efficiency' => $data['salvage_extraction_efficiency'] ?? null,
        ]);
    }
}
