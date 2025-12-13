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
        Schema::create('game_vehicle_data', static function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vehicle_id');
            $table->unsignedBigInteger('game_version_id')->index();
            $table->unsignedBigInteger('manufacturer_id')->nullable();
            $table->unsignedBigInteger('shipmatrix_id')->nullable();

            $table->string('class_name');
            $table->string('name')->nullable();
            $table->string('career')->nullable();
            $table->string('role')->nullable();

            $table->boolean('is_vehicle')->default(false);
            $table->boolean('is_gravlev')->default(false);
            $table->boolean('is_spaceship')->default(false);

            $table->unsignedInteger('size')->nullable();
            $table->double('length')->nullable();
            $table->double('width')->nullable();
            $table->double('height')->nullable();
            $table->unsignedInteger('crew')->nullable();
            $table->double('mass')->nullable();
            $table->double('cargo')->nullable();

            $table->double('insurance_claim_time')->nullable();
            $table->double('insurance_expedited_time')->nullable();
            $table->double('insurance_expedited_cost')->nullable();

            $table->string('shield_face_type')->nullable();
            $table->double('shield_hp')->nullable();
            $table->double('health')->nullable();

            $table->double('quantum_speed')->nullable();
            $table->double('quantum_spool_time')->nullable();
            $table->double('quantum_fuel_capacity')->nullable();
            $table->double('quantum_range')->nullable();

            $table->double('fuel_capacity')->nullable();
            $table->double('fuel_intake_rate')->nullable();
            $table->double('fuel_usage_main')->nullable();
            $table->double('fuel_usage_retro')->nullable();
            $table->double('fuel_usage_vtol')->nullable();
            $table->double('fuel_usage_maneuvering')->nullable();

            $table->jsonb('json');
            $table->timestamps();

            $table->index(['vehicle_id', 'game_version_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_vehicle_data');
    }
};
