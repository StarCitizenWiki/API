<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScUnpackedShipItemDistortionDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('star_citizen_unpacked_ship_item_distortion_data', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ship_item_id');

            $table->unsignedDouble('decay_rate')->nullable();

            $table->unsignedDouble('maximum')->nullable();

            $table->unsignedDouble('overload_ratio')->nullable();

            $table->unsignedDouble('recovery_ratio')->nullable();
            $table->unsignedDouble('recovery_time')->nullable();

            $table->foreign('ship_item_id', 'distortion_data_id_ship_item_id')
                ->references('ud')
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
        Schema::dropIfExists('star_citizen_unpacked_ship_item_distortion_data');
    }
}
