<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScUnpackedShipMissilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('star_citizen_unpacked_ship_missiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ship_item_id');
            $table->string('uuid')->unique();
            $table->unsignedDouble('speed');
            $table->unsignedDouble('range');
            $table->unsignedDouble('size');
            $table->unsignedDouble('capacity')->nullable();

            $table->unsignedDouble('damage_physical')->nullable();
            $table->unsignedDouble('damage_energy')->nullable();
            $table->unsignedDouble('damage_distortion')->nullable();
            $table->unsignedDouble('damage_thermal')->nullable();
            $table->unsignedDouble('damage_biochemical')->nullable();
            $table->unsignedDouble('damage_stun')->nullable();

            $table->unsignedDouble('detonation_damage_physical')->nullable();
            $table->unsignedDouble('detonation_damage_energy')->nullable();
            $table->unsignedDouble('detonation_damage_distortion')->nullable();
            $table->unsignedDouble('detonation_damage_thermal')->nullable();
            $table->unsignedDouble('detonation_damage_biochemical')->nullable();
            $table->unsignedDouble('detonation_damage_stun')->nullable();
            $table->timestamps();

            $table->foreign('ship_item_id', 'weapons_ship_item_id')
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
        Schema::dropIfExists('star_citizen_unpacked_ship_missiles');
    }
}
