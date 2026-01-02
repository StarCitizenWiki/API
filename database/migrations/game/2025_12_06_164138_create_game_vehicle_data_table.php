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
            $table->unsignedBigInteger('shipmatrix_id')->nullable()->index();

            $table->string('class_name');
            $table->string('name')->nullable();
            $table->string('display_name')->nullable();
            $table->string('career')->nullable();
            $table->string('role')->nullable();

            $table->boolean('is_vehicle')->default(false);
            $table->boolean('is_gravlev')->default(false);
            $table->boolean('is_spaceship')->default(false);

            $table->unsignedInteger('size')->nullable();

            $table->jsonb('data');

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
