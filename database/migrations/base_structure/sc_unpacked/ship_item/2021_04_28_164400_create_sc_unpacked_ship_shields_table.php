<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScUnpackedShipShieldsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('star_citizen_unpacked_ship_shields', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ship_item_id');
            $table->string('uuid')->unique();
            $table->unsignedDouble('health');
            $table->unsignedDouble('regeneration');
            $table->unsignedDouble('downed_delay');
            $table->unsignedDouble('damage_delay');
            $table->unsignedDouble('min_physical_absorption');
            $table->unsignedDouble('max_physical_absorption');
            $table->unsignedDouble('min_energy_absorption');
            $table->unsignedDouble('max_energy_absorption');
            $table->unsignedDouble('min_distortion_absorption');
            $table->unsignedDouble('max_distortion_absorption');
            $table->unsignedDouble('min_thermal_absorption');
            $table->unsignedDouble('max_thermal_absorption');
            $table->unsignedDouble('min_biochemical_absorption');
            $table->unsignedDouble('max_biochemical_absorption');
            $table->unsignedDouble('min_stun_absorption');
            $table->unsignedDouble('max_stun_absorption');
            $table->timestamps();

            $table->foreign('ship_item_id', 'shields_ship_item_id')
                ->references('id')
                ->on('star_citizen_unpacked_ship_items')
                ->onDelete('cascade');

            $table->foreign('uuid', 'shields_ship_item_uuid')
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
        Schema::dropIfExists('star_citizen_unpacked_ship_shields');
    }
}
