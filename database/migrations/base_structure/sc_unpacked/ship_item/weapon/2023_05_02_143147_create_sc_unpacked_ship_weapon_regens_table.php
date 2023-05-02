<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('star_citizen_unpacked_ship_weapon_regens', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ship_weapon_id');
            $table->unsignedDouble('requested_regen_per_sec')->nullable();
            $table->unsignedDouble('requested_ammo_load')->nullable();
            $table->unsignedDouble('cooldown')->nullable();
            $table->unsignedDouble('cost_per_bullet')->nullable();

            $table->timestamps();

            $table->foreign('ship_weapon_id', 'ship_weapon_regen_id')
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
        Schema::dropIfExists('star_citizen_unpacked_ship_weapon_regens');
    }
};
