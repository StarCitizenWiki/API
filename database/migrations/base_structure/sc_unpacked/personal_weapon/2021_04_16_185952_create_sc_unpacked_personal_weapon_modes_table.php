<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScUnpackedPersonalWeaponModesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('star_citizen_unpacked_personal_weapon_modes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('weapon_id');
            $table->string('mode');
            $table->string('localised');
            $table->string('type');
            $table->unsignedDouble('rounds_per_minute');
            $table->unsignedDouble('ammo_per_shot');
            $table->unsignedDouble('pellets_per_shot');

            $table->timestamps();

            $table->foreign('weapon_id', 'weapon_weapon_id_foreign')
                ->references('id')
                ->on('star_citizen_unpacked_personal_weapons')
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
        Schema::dropIfExists('star_citizen_unpacked_personal_weapon_modes');
    }
}
