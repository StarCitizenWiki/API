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
        Schema::create('sc_vehicles', static function (Blueprint $table) {
            $table->id();
            $table->uuid('item_uuid');
            $table->unsignedInteger('shipmatrix_id');
            $table->string('class_name')->unique();
            $table->string('name');
            $table->string('career');
            $table->string('role');
            $table->boolean('is_ship')->default(true);
            $table->unsignedInteger('size');

            $table->unsignedDouble('width');
            $table->unsignedDouble('height');
            $table->unsignedDouble('length');

            $table->unsignedInteger('crew');
            $table->unsignedInteger('weapon_crew');
            $table->unsignedInteger('operations_crew');
            $table->unsignedBigInteger('mass');

            $table->unsignedDouble('zero_to_scm');
            $table->unsignedDouble('zero_to_max');

            $table->unsignedDouble('scm_to_zero');
            $table->unsignedDouble('max_to_zero');

            $table->unsignedDouble('acceleration_main');
            $table->unsignedDouble('acceleration_retro');
            $table->unsignedDouble('acceleration_vtol');
            $table->unsignedDouble('acceleration_maneuvering');

            $table->unsignedDouble('acceleration_g_main');
            $table->unsignedDouble('acceleration_g_retro');
            $table->unsignedDouble('acceleration_g_vtol');
            $table->unsignedDouble('acceleration_g_maneuvering');

            $table->unsignedDouble('claim_time');
            $table->unsignedDouble('expedite_time');
            $table->unsignedDouble('expedite_cost');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('sc_vehicles');
    }
};
