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
    public function up(): void
    {
        Schema::create('sc_personal_weapon_modes', static function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('weapon_id');
            $table->string('mode');
            $table->string('localised');
            $table->string('type');
            $table->unsignedDouble('rounds_per_minute');
            $table->unsignedDouble('ammo_per_shot');
            $table->unsignedDouble('pellets_per_shot');
            $table->timestamps();

            $table->foreign('weapon_id', 'fk_sc_p_w_mod_weapon_id')
                ->references('id')
                ->on('sc_personal_weapons')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('sc_personal_weapon_modes');
    }
};
