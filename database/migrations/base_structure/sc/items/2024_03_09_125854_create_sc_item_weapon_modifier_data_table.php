<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sc_item_weapon_modifier_data', static function (Blueprint $table) {
            $table->id();
            $table->string('item_uuid')->unique();

            $table->double('fire_rate_multiplier')->nullable();
            $table->double('damage_multiplier')->nullable();
            $table->double('damage_over_time_multiplier')->nullable();
            $table->double('projectile_speed_multiplier')->nullable();
            $table->double('ammo_cost_multiplier')->nullable();
            $table->double('heat_generation_multiplier')->nullable();
            $table->double('sound_radius_multiplier')->nullable();
            $table->double('charge_time_multiplier')->nullable();

            $table->double('recoil_decay_multiplier')->nullable();
            $table->double('recoil_end_decay_multiplier')->nullable();
            $table->double('recoil_fire_recoil_time_multiplier')->nullable();
            $table->double('recoil_fire_recoil_strength_first_multiplier')->nullable();
            $table->double('recoil_fire_recoil_strength_multiplier')->nullable();
            $table->double('recoil_angle_recoil_strength_multiplier')->nullable();
            $table->double('recoil_randomness_multiplier')->nullable();
            $table->double('recoil_randomness_back_push_multiplier')->nullable();
            $table->double('recoil_frontal_oscillation_rotation_multiplier')->nullable();
            $table->double('recoil_frontal_oscillation_strength_multiplier')->nullable();
            $table->double('recoil_frontal_oscillation_decay_multiplier')->nullable();
            $table->double('recoil_frontal_oscillation_randomness_multiplier')->nullable();
            $table->double('recoil_animated_recoil_multiplier')->nullable();

            $table->double('spread_min_multiplier')->nullable();
            $table->double('spread_max_multiplier')->nullable();
            $table->double('spread_first_attack_multiplier')->nullable();
            $table->double('spread_attack_multiplier')->nullable();
            $table->double('spread_decay_multiplier')->nullable();
            $table->double('spread_additive_modifier')->nullable();

            $table->double('aim_zoom_scale')->nullable();
            $table->double('aim_zoom_time_scale')->nullable();

            $table->double('salvage_speed_multiplier')->nullable();
            $table->double('salvage_radius_multiplier')->nullable();
            $table->double('salvage_extraction_efficiency')->nullable();
            $table->timestamps();

            $table->foreign('item_uuid', 'sc_i_w_m_dat_item_uuid')
                ->references('uuid')
                ->on('sc_items')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sc_item_weapon_modifier_data');
    }
};
