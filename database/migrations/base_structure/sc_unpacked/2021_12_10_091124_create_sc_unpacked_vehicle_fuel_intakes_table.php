<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScUnpackedVehicleFuelIntakesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('star_citizen_unpacked_vehicle_fuel_intakes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ship_item_id');
            $table->string('uuid')->unique();

            $table->unsignedDouble('fuel_push_rate');
            $table->unsignedDouble('minimum_rate');

            $table->timestamps();

            $table->foreign('ship_item_id', 'intake_vehicle_item_id')
                ->references('id')
                ->on('star_citizen_unpacked_ship_items')
                ->onDelete('cascade');

            $table->foreign('uuid', 'intake_vehicle_item_uuid')
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
        Schema::dropIfExists('star_citizen_unpacked_vehicle_fuel_intakes');
    }
}
