<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScUnpackedVehicleHardpointTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('star_citizen_unpacked_vehicle_hardpoint', function (Blueprint $table) {
            $table->unsignedBigInteger('vehicle_id');
            $table->unsignedBigInteger('hardpoint_id');
            $table->unsignedBigInteger('parent_hardpoint_id')->nullable();

            $table->uuid('equipped_vehicle_item_uuid')->nullable();

            $table->unsignedInteger('min_size')->nullable();
            $table->unsignedInteger('max_size')->nullable();

            $table->index('vehicle_id');
            $table->index('hardpoint_id');

            $table->foreign('vehicle_id', 'vehicle_hardpoint_vehicle_id')
                ->references('id')
                ->on('star_citizen_unpacked_vehicles')
                ->onDelete('cascade');

            $table->foreign('hardpoint_id', 'vehicle_hardpoint_hardpoint_id')
                ->references('id')
                ->on('star_citizen_unpacked_vehicle_hardpoints')
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
        Schema::dropIfExists('star_citizen_unpacked_vehicle_hardpoint');
    }
}
