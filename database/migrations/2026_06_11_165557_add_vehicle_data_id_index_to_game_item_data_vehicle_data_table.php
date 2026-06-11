<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('game_item_data_vehicle_data', function (Blueprint $table) {
            $table->index('vehicle_data_id');
        });
    }

    public function down(): void
    {
        Schema::table('game_item_data_vehicle_data', function (Blueprint $table) {
            $table->dropIndex(['vehicle_data_id']);
        });
    }
};
