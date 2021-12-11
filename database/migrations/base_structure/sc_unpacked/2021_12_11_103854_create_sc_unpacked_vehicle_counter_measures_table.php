<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScUnpackedVehicleCounterMeasuresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('star_citizen_unpacked_vehicle_counter_measures', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ship_item_id');
            $table->string('uuid')->unique();

            $table->unsignedDouble('initial_ammo_count');
            $table->unsignedDouble('max_ammo_count');

            $table->timestamps();

            $table->foreign('ship_item_id', 'cm_vehicle_item_id')
                ->references('id')
                ->on('star_citizen_unpacked_ship_items')
                ->onDelete('cascade');

            $table->foreign('uuid', 'cm_vehicle_item_uuid')
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
        Schema::dropIfExists('star_citizen_unpacked_vehicle_counter_measures');
    }
}
