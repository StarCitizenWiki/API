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
        Schema::create('game_item_data_commodity', static function (Blueprint $table) {
            $table->foreignId('item_data_id')->constrained('game_item_data')->cascadeOnDelete();
            $table->foreignId('commodity_id')->constrained('game_commodities')->cascadeOnDelete();

            $table->primary(['item_data_id', 'commodity_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_item_data_commodity');
    }
};
