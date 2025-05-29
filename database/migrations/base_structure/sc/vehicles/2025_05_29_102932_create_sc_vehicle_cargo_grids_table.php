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
        Schema::create('sc_vehicle_cargo_grids', static function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vehicle_id');
            $table->uuid('container_uuid');
            $table->unsignedInteger('capacity');
            $table->text('unit_name');

            $table->unsignedDouble('x')->nullable();
            $table->unsignedDouble('y')->nullable();
            $table->unsignedDouble('z')->nullable();

            $table->unsignedDouble('min_x')->nullable();
            $table->unsignedDouble('min_y')->nullable();
            $table->unsignedDouble('min_z')->nullable();

            $table->unsignedDouble('max_x')->nullable();
            $table->unsignedDouble('max_y')->nullable();
            $table->unsignedDouble('max_z')->nullable();

            $table->boolean('is_open')->default(false);
            $table->boolean('is_external')->default(false);
            $table->boolean('is_closed')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sc_vehicle_cargo_grids');
    }
};
