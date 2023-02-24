<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScUnpackedShipItemHeatDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('star_citizen_unpacked_ship_item_heat_data', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ship_item_id');

            $table->unsignedDouble('temperature_to_ir')->nullable();

            $table->unsignedDouble('overpower_heat')->nullable();

            $table->unsignedDouble('overclock_threshold_min')->nullable();
            $table->unsignedDouble('overclock_threshold_max')->nullable();

            $table->unsignedDouble('thermal_energy_base')->nullable();
            $table->unsignedDouble('thermal_energy_draw')->nullable();
            $table->unsignedDouble('thermal_conductivity')->nullable();

            $table->unsignedDouble('specific_heat_capacity')->nullable();

            $table->unsignedDouble('mass')->nullable();
            $table->unsignedDouble('surface_area')->nullable();

            $table->unsignedDouble('start_cooling_temperature')->nullable();
            $table->unsignedDouble('max_cooling_rate')->nullable();

            $table->unsignedDouble('max_temperature')->nullable();
            $table->unsignedDouble('min_temperature')->nullable();
            $table->unsignedDouble('overheat_temperature')->nullable();
            $table->unsignedDouble('recovery_temperature')->nullable();

            $table->unsignedDouble('misfire_min_temperature')->nullable();
            $table->unsignedDouble('misfire_max_temperature')->nullable();

            $table->foreign('ship_item_id', 'heat_data_id_ship_item_id')
                ->references('id')
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
        Schema::dropIfExists('star_citizen_unpacked_ship_item_heat_data');
    }
}
