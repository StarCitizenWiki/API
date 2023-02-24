<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScUnpackedVehicleMiningLasersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('star_citizen_unpacked_vehicle_mining_lasers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ship_item_id');
            $table->string('uuid')->unique();
            $table->string('hit_type');
            $table->unsignedDouble('energy_rate');
            $table->unsignedDouble('full_damage_range');
            $table->unsignedDouble('zero_damage_range');
            $table->unsignedDouble('heat_per_second');
            $table->unsignedDouble('damage');

            $table->double('modifier_resistance');
            $table->double('modifier_instability');
            $table->double('modifier_charge_window_size');
            $table->double('modifier_charge_window_rate');
            $table->double('modifier_shatter_damage');
            $table->double('modifier_catastrophic_window_rate');

            $table->unsignedDouble('consumable_slots');

            $table->timestamps();

            $table->foreign('ship_item_id', 'mining_lasers_ship_item_id')
                ->references('id')
                ->on('star_citizen_unpacked_ship_items')
                ->onDelete('cascade');

            $table->foreign('uuid', 'mining_lasers_ship_item_uuid')
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
        Schema::dropIfExists('star_citizen_unpacked_vehicle_mining_lasers');
    }
}
