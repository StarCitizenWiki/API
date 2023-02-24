<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScUnpackedShipItemDurabilityDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('star_citizen_unpacked_ship_item_durability_data', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ship_item_id');

            $table->unsignedDouble('health')->nullable();

            $table->unsignedDouble('max_lifetime')->nullable();

            $table->foreign('ship_item_id', 'durability_data_id_ship_item_id')
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
        Schema::dropIfExists('star_citizen_unpacked_ship_item_durability_data');
    }
}
