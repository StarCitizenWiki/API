<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('sc_item_quantum_drives', static function (Blueprint $table) {
            $table->id();
            $table->uuid('item_uuid')->unique();

            $table->unsignedDouble('quantum_fuel_requirement');
            $table->string('jump_range');
            $table->unsignedDouble('disconnect_range');

            $table->unsignedDouble('pre_ramp_up_thermal_energy_draw');
            $table->unsignedDouble('ramp_up_thermal_energy_draw');
            $table->unsignedDouble('in_flight_thermal_energy_draw');
            $table->unsignedDouble('ramp_down_thermal_energy_draw');
            $table->unsignedDouble('post_ramp_down_thermal_energy_draw');
            $table->timestamps();

            $table->foreign('item_uuid', 'fk_sc_i_q_dri_item_uuid')
                ->references('uuid')
                ->on('sc_items')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('sc_item_quantum_drives');
    }
};
