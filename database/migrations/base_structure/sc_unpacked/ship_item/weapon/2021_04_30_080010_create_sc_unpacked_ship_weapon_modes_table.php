<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScUnpackedShipWeaponModesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('star_citizen_unpacked_ship_weapon_modes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ship_weapon_id');
            $table->string('mode');
            $table->string('localised');
            $table->string('type');
            $table->unsignedDouble('rounds_per_minute')->nullable();
            $table->unsignedDouble('ammo_per_shot')->nullable();
            $table->unsignedDouble('pellets_per_shot')->nullable();
            $table->timestamps();

            $table->foreign('ship_weapon_id', 'ship_weapon_mode_ship_weapon_id')
                ->references('id')
                ->on('star_citizen_unpacked_ship_weapons')
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
        Schema::dropIfExists('star_citizen_unpacked_ship_weapon_modes');
    }
}
