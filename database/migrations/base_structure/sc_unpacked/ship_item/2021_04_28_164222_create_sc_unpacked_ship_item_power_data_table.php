<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScUnpackedShipItemPowerDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('star_citizen_unpacked_ship_item_power_data', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ship_item_id');

            $table->unsignedDouble('power_base')->nullable();
            $table->unsignedDouble('power_draw')->nullable();

            $table->boolean('throttleable')->default(false);
            $table->boolean('overclockable')->default(false);

            $table->unsignedDouble('overclock_threshold_min')->nullable();
            $table->unsignedDouble('overclock_threshold_max')->nullable();
            $table->unsignedDouble('overclock_performance')->nullable();

            $table->unsignedDouble('overpower_performance')->nullable();

            $table->unsignedDouble('power_to_em')->nullable();
            $table->unsignedDouble('decay_rate_em')->nullable();

            $table->foreign('ship_item_id', 'power_data_id_ship_item_id')
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
        Schema::dropIfExists('star_citizen_unpacked_ship_item_power_data');
    }
}
