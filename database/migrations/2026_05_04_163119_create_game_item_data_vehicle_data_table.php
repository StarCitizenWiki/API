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
        Schema::create('game_item_data_vehicle_data', static function (Blueprint $table) {
            $table->foreignId('item_data_id')->constrained('game_item_data')->cascadeOnDelete();
            $table->foreignId('vehicle_data_id')->constrained('game_vehicle_data')->cascadeOnDelete();

            $table->primary(['item_data_id', 'vehicle_data_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_item_data_vehicle_data');
    }
};
