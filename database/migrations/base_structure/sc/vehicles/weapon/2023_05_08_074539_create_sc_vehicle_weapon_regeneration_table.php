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
        Schema::create('sc_vehicle_weapon_regeneration', static function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('weapon_id');
            $table->unsignedDouble('requested_regen_per_sec')->nullable();
            $table->unsignedDouble('requested_ammo_load')->nullable();
            $table->unsignedDouble('cooldown')->nullable();
            $table->unsignedDouble('cost_per_bullet')->nullable();

            $table->timestamps();

            $table->foreign('weapon_id', 'sc_v_w_reg_weapon_id')
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
        Schema::dropIfExists('sc_vehicle_weapon_regeneration');
    }
};
