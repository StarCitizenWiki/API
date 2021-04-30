<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScUnpackedShipQuantumDrivesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('star_citizen_unpacked_ship_quantum_drives', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ship_item_id');
            $table->string('uuid')->unique();

            $table->unsignedDouble('quantum_fuel_requirement');
            $table->string('jump_range');
            $table->unsignedDouble('disconnect_range');

            $table->unsignedDouble('pre_ramp_up_thermal_energy_draw');
            $table->unsignedDouble('ramp_up_thermal_energy_draw');
            $table->unsignedDouble('in_flight_thermal_energy_draw');
            $table->unsignedDouble('ramp_down_thermal_energy_draw');
            $table->unsignedDouble('post_ramp_down_thermal_energy_draw');

            $table->timestamps();

            $table->foreign('ship_item_id', 'quantum_drive_ship_item_id')
                ->references('id')
                ->on('star_citizen_unpacked_ship_items')
                ->onDelete('cascade');

            $table->foreign('uuid', 'quantum_drive_ship_item_uuid')
                ->references('uuid')
                ->on('star_citizen_unpacked_ship_items')
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
        Schema::dropIfExists('star_citizen_unpacked_ship_quantum_drives');
    }
}
