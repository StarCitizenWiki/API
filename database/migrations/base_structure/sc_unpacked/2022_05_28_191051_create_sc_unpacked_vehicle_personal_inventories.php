<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScUnpackedVehiclePersonalInventories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('star_citizen_unpacked_vehicle_personal_inventories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ship_item_id');
            $table->string('uuid')->unique();

            $table->unsignedDouble('scu');

            $table->timestamps();

            $table->foreign('ship_item_id', 'personal_inventory_vehicle_item_id')
                ->references('id')
                ->on('star_citizen_unpacked_ship_items')
                ->onDelete('cascade');

            $table->foreign('uuid', 'personal_inventory_vehicle_item_uuid')
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
        Schema::dropIfExists('star_citizen_unpacked_vehicle_personal_inventories');
    }
}
