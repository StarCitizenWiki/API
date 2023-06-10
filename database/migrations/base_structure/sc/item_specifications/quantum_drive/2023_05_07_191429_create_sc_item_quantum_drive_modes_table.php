<?php

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
        Schema::create('sc_item_quantum_drive_modes', static function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('quantum_drive_id');

            $table->string('type');

            $table->unsignedDouble('drive_speed');
            $table->unsignedDouble('cooldown_time');
            $table->unsignedDouble('stage_one_accel_rate');
            $table->unsignedDouble('stage_two_accel_rate');
            $table->unsignedDouble('engage_speed');
            $table->unsignedDouble('interdiction_effect_time');
            $table->unsignedDouble('calibration_rate');
            $table->unsignedDouble('min_calibration_requirement');
            $table->unsignedDouble('max_calibration_requirement');
            $table->unsignedDouble('calibration_process_angle_limit');
            $table->unsignedDouble('calibration_warning_angle_limit');
            $table->unsignedDouble('spool_up_time');

            $table->timestamps();

            $table->foreign('quantum_drive_id', 'fk_sc_i_q_d_mod_quantum_drive_id')
                ->references('id')
                ->on('sc_item_quantum_drives')
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
        Schema::dropIfExists('sc_item_quantum_drive_modes');
    }
};
