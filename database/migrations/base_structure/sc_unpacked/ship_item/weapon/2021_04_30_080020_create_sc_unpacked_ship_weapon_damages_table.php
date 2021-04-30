<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScUnpackedShipWeaponDamagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('star_citizen_unpacked_ship_weapon_damages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ship_weapon_id');
            $table->string('type');
            $table->string('name');

            $table->unsignedDouble('damage');

            $table->timestamps();

            $table->foreign('ship_weapon_id', 'ship_weapon_damage_id')
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
        Schema::dropIfExists('star_citizen_unpacked_ship_weapon_damages');
    }
}
