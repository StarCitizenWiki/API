<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropStarCitizenUnpackedPersonalWeaponMagazinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('star_citizen_unpacked_personal_weapon_magazines');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('star_citizen_unpacked_personal_weapon_magazines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('weapon_id');
            $table->unsignedDouble('initial_ammo_count');
            $table->unsignedDouble('max_ammo_count');
            $table->timestamps();

            $table->foreign('weapon_id', 'magazine_weapon_id_foreign')
                ->references('id')
                ->on('star_citizen_unpacked_personal_weapons')
                ->onDelete('cascade');
        });
    }
}
