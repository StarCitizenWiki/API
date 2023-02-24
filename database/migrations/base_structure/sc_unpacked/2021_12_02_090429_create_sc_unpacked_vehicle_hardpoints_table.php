<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScUnpackedVehicleHardpointsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('star_citizen_unpacked_vehicle_hardpoints', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vehicle_id');
            $table->unsignedBigInteger('parent_hardpoint_id')->nullable();

            $table->string('hardpoint_name');

            $table->uuid('equipped_vehicle_item_uuid')->nullable();

            $table->unsignedInteger('min_size')->nullable();
            $table->unsignedInteger('max_size')->nullable();

            $table->string('class_name')->nullable();

            $table->index('vehicle_id');
            $table->index('parent_hardpoint_id', 'hardpoint_parent_index');

            $table->foreign('vehicle_id', 'vehicle_hardpoint_vehicle_id')
                ->references('id')
                ->on('star_citizen_unpacked_vehicles')
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
        Schema::dropIfExists('star_citizen_unpacked_vehicle_hardpoints');
    }
}
