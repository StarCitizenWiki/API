<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScUnpackedShipQuantumDriveModesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('star_citizen_unpacked_ship_quantum_drive_modes', function (Blueprint $table) {
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

            $table->foreign('quantum_drive_id', 'quantum_drive_mode_quantum_drive_id')
                ->references('id')
                ->on('star_citizen_unpacked_ship_quantum_drives')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('star_citizen_unpacked_ship_quantum_drive_modes');
    }
}
