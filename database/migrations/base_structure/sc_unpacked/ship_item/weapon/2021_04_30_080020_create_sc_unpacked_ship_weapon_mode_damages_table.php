<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScUnpackedShipWeaponModeDamagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('star_citizen_unpacked_ship_weapon_mode_damages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ship_weapon_mode_id');
            $table->enum('type', ['shot', 'second']);
            $table->string('name');
            $table->unsignedDouble('damage');

            $table->foreign('ship_weapon_mode_id', 'ship_weapon_mode_damage_id')
                ->references('id')
                ->on('star_citizen_unpacked_ship_weapon_modes')
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
        Schema::dropIfExists('star_citizen_unpacked_ship_weapon_mode_damages');
    }
}
