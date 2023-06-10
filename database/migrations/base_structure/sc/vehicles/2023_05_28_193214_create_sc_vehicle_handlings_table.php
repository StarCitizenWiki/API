<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sc_vehicle_handlings', static function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vehicle_id');
            $table->unsignedDouble('max_speed');
            $table->unsignedDouble('reverse_speed');
            $table->unsignedDouble('acceleration');
            $table->unsignedDouble('deceleration');
            $table->unsignedDouble('v0_steer_max');
            $table->unsignedDouble('kv_steer_max');
            $table->unsignedDouble('vmax_steer_max');

            $table->timestamps();

            $table->foreign('vehicle_id', 'fk_sc_v_han_vehicle_id')
                ->references('id')
                ->on('sc_vehicles')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sc_vehicle_handlings');
    }
};
