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
        Schema::create('sc_vehicle_weapon_modes', function (Blueprint $table) {
            $table->id();            
            $table->unsignedBigInteger('weapon_id');
            $table->string('mode')->nullable();
            $table->string('localised')->nullable();
            $table->string('type')->nullable();
            $table->unsignedDouble('rounds_per_minute')->nullable();
            $table->unsignedDouble('ammo_per_shot')->nullable();
            $table->unsignedDouble('pellets_per_shot')->nullable();
            $table->timestamps();

            $table->foreign('weapon_id', 'fk_sc_v_w_mod_vehicle_weapon_id')
                ->references('id')
                ->on('sc_vehicle_weapons')
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
        Schema::dropIfExists('sc_vehicle_weapon_modes');
    }
};
