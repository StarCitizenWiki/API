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
        Schema::create('sc_vehicle_weapon_ammunition_damages', static function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ammunition_id');
            $table->string('type');
            $table->string('name');

            $table->unsignedDouble('damage');

            $table->timestamps();

            $table->foreign('ammunition_id', 'fk_sc_v_w_a_dam_ammunition_id')
                ->references('id')
                ->on('sc_vehicle_weapon_ammunitions')
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
        Schema::dropIfExists('sc_vehicle_weapon_ammunition_damages');
    }
};
